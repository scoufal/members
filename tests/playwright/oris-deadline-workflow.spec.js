const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const { loginAs, postFormInSession } = require('./helpers/browser');

function fixture(action = 'read', fields) {
  return JSON.parse(execFileSync('php', ['-d', 'short_open_tag=1', path.join(__dirname, 'helpers/oris-deadline-workflow-fixture.php')], {
    input: JSON.stringify({ action, fields }), encoding: 'utf8', stdio: ['pipe', 'pipe', 'pipe'],
  }));
}

test.describe('ORIS-linked deadline workflow', () => {
  test.afterEach(() => fixture('cleanup'));

  test('small manager keeps an expired ORIS-linked entry local', async ({ page, browser }) => {
    const state = fixture('reset');
    await loginAs(page, 'member');
    let result = await postFormInSession(page, './us_race_regon_exc.php', {
      id_zav: state.id, id_us: state.member, novy: 1, kat: 'H35', sedadel: 2, ubytovani: 1,
    });
    expect(result.status, result.text).toBe(200);

    fixture('patch', { ext_id: 8971, prihlasky1: Math.floor(Date.now() / 1000) - 60, transport_do: Math.floor(Date.now() / 1000) + 600 });
    fixture('patch_entry');
    const managerContext = await browser.newContext({ timezoneId: 'Europe/Prague' });
    const managerPage = await managerContext.newPage();
    await loginAs(managerPage, 'smallManager');
    result = await postFormInSession(managerPage, `./race_regs_1_exc.php?gr_id=600&id=${state.id}&show_ed=1`, {
      user_id: state.member, kateg: 'ignored-after-deadline', sedadel: 4,
    });
    await managerContext.close();
    expect(result.status, result.text).toBe(200);
    expect(fixture().entry.sedadel).toBe('4');
    expect(fixture().entry.sync_status).toBe('SYNCED');
  });
});
