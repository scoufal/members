<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Zavod</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <style>

        .ui-selectmenu-button.ui-button {
            width: 85%;
        }

        .active {
            background-color: darkorange;
        }

    </style>

  <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <script>
  $( function() {

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    var race = urlParams.get('race_id');
    document.getElementById("title").innerHTML = "Zavod "+race;
    var dat = [];
    var accordion = document.getElementById("accordion");
    $.getJSON('api_race_entry.php?id_race='+race, function(data) {
        $( "#accordion" ).html('');
        data.forEach(el => {
            var flds_entry = '<label for="checkbox-1-'+el.reg+'">Přihlášen</label><input type="checkbox" name="checkbox-1-'+el.reg+'" id="checkbox-1-'+el.reg+'"><label for="checkbox-2-'+el.reg+'">Účast</label><input type="checkbox" name="checkbox-2-'+el.reg+'" id="checkbox-2-'+el.reg+'">';
            var flds_kat = '<select id="cat-'+el.reg+'" class="select-cat"></select>';
            var flds_note = '<input type="text" value="sem napis poznamku ..."></input>';
            
            // tlacitka na prihlaseni a ucast v headru accordionu
            var head_span_prihlasen = '<button style="margin-right:2px;"onClick="tickEntry(this,'+el.reg+','+race+')" '+(el.add_by_fin==1?'class="active"':'')+'>Prihl.</button>';
            var head_span_ucast = '<button onClick="tickParticipate(this,'+el.id_user+','+race+')" '+(el.participated==1?'class="active"':'')+'>Ucast</button>';
            var head_span = '<span style="float:right;" class="toolbar ui-widget-header ui-corner-all">'+(el.kat?'':head_span_prihlasen)+head_span_ucast+'</span>';

            $( accordion ).append('<h3>'+el.id_user+'|'+el.reg+'::'+el.name+' '+head_span+'</h3><div id="div-'+el.reg+'"></div>');
            var div_entry_data = $( "#div-"+el.reg);
            $( div_entry_data ).append(flds_note).append(flds_kat).append(flds_entry);
            if(el.kat) $( "#checkbox-1-"+el.reg).prop("checked", true );
            var sel_cat = $( "#cat-"+el.reg );
            var arr_cat = ['vyber kategorii','h21','d21'];
            arr_cat.forEach(el_cat => {
                $( sel_cat ).append('<option id="cat-opt-'+el.reg+'-'+el_cat+'" value="'+el_cat+' selected">'+el_cat+'</option>');
            });
        });
        $( accordion ).accordion("refresh");

        $( "input:checkbox" ).checkboxradio({
            icon: false
        });


    });

    $( accordion ).accordion({
        active: false,
        collapsible: true,
        heightStyle: "content"
    });

    $("#search").keyup(function(){
        var searchedText = $('#search').val().toString().toLowerCase();
        $( "h3" ).each(function(){
            var htxt=$(this).text().toString().toLowerCase();
            if (htxt.indexOf(searchedText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

  });

  function tickEntry(elem, id_user, id_race) {
    event.stopPropagation(); // this is
    event.preventDefault(); // the magic
    $.getJSON('api_race_entry.php?id_race='+id_race+'&action=addByFin&value=insert&id_user='+id_user, function(data) {
        console.log('entry :: '+data)
    }).done(function(result) {
        if (result > 0) {
            $( elem ).toggleClass("active");
        }
    });
  }

  function tickParticipate(elem, id_user, id_race) {
    event.stopPropagation(); // this is
    event.preventDefault(); // the magic
    $.getJSON('api_race_entry.php?id_race='+id_race+'&action=participate&id_user='+id_user, function(data) {
        console.log('participate :: '+data)
    }).done(function(result) {
        if (result > 0) {
            $( elem ).toggleClass("active");
        }
    });
  }


  </script>
</head>
<body>
<h2 id='title'>Zavod id=582, 50 lidi, prvni 2 jsou prihlaseni</h2>
<input id="search" placeholder="filtr podle jmena"/>
<div id="accordion">
Nacitam data zavodu ...
</div>

</body>
</html>