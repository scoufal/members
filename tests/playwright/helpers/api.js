const { login } = require('../components/login');
const { TEST_USERS } = require('../constants/users');

async function withAuthenticatedPage(browser, username, callback, password) {
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    await login(page, username, password);
    return await callback(page);
  } finally {
    await context.close();
  }
}

async function getCurrentUser(browser, username, password) {
  return withAuthenticatedPage(browser, username, async (page) => {
    await page.goto('./index.php?id=200&subid=3');

    return page.locator('form[action*="user_new_exc.php?update="]').evaluate((form) => {
      const userId = (form.getAttribute('action') || '').match(/update=(\d+)/)?.[1];
      const value = (name) => form.querySelector(`[name="${name}"]`)?.value || '';
      const registrationNumber = value('reg').match(/(\d+)$/)?.[1] || '';

      return {
        user_id: Number(userId),
        name: value('jmeno'),
        surname: value('prijmeni'),
        registration_number: registrationNumber,
        chip_number: value('si'),
        chief_id: Number(form.querySelector('input[name="chief_pay"]')?.value || 0),
      };
    });
  }, password);
}

async function getManagingUsers(browser, username) {
  return withAuthenticatedPage(browser, username, async (page) => {
    await page.goto('./index.php?id=600&subid=1');

    return page.locator('tr').evaluateAll((rows) => rows.map((row) => {
      const editLink = Array.from(row.querySelectorAll('a')).find((link) => (
        /mns_user_edit\.php\?id=\d+/.test(link.getAttribute('href') || '')
      ));
      const userId = (editLink?.getAttribute('href') || '').match(/[?&]id=(\d+)/)?.[1];
      return userId ? { user_id: Number(userId) } : null;
    }).filter(Boolean));
  });
}

async function getRaceDetail(browser, raceId) {
  return withAuthenticatedPage(browser, TEST_USERS.registrar, async (page) => {
    await page.goto(`./race_edit.php?id=${raceId}`);
    const race = await page.locator('form[name="form2"]').evaluate((form) => ({
      name: form.querySelector('[name="nazev"]')?.value || '',
      rankings: Array.from(form.querySelectorAll('input[name^="zebricek["]:checked'))
        .map((input) => form.querySelector(`label[for="${input.id}"]`)?.textContent?.trim())
        .filter(Boolean),
    }));

    await page.goto(`./race_regs_all.php?gr_id=400&id=${raceId}`);
    const everyone = await page.locator('input[name^="kateg["]').evaluateAll((inputs) => inputs.map((input) => {
      const userId = input.getAttribute('name')?.match(/\[(\d+)\]/)?.[1];
      const category = input.value.trim();
      const row = input.closest('tr');
      const note = row?.querySelector(`input[name="pozn[${userId}]"]`)?.value || '';
      const noteInternal = row?.querySelector(`input[name="pozn2[${userId}]"]`)?.value || '';
      return userId && category ? {
        user_id: Number(userId),
        category,
        note,
        note_internal: noteInternal,
      } : null;
    }).filter(Boolean));
    const categories = await page.locator('button').evaluateAll((buttons) => buttons
      .map((button) => button.textContent.trim())
      .filter((label) => /^[HD]\d+(?:[A-Z]+)?$/.test(label)));

    return { ...race, categories, everyone };
  });
}

async function findRaceByName(page, raceName) {
  await page.goto('./index.php?id=400&subid=4&fC=1');
  const raceLink = page.getByRole('link', { name: raceName, exact: true }).first();
  const row = raceLink.locator('xpath=ancestor::tr[1]');
  const hrefs = await row.locator('a').evaluateAll((links) => links.map((link) => link.getAttribute('href') || ''));
  const href = hrefs.find((value) => /race_edit\.php\?id=\d+/.test(value));
  const id = href?.match(/[?&]id=(\d+)/)?.[1];

  return id ? { id: Number(id), name: raceName } : null;
}

module.exports = {
  findRaceByName,
  getCurrentUser,
  getManagingUsers,
  getRaceDetail,
};
