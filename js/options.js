/*$('#pais').change(function(){
    $('#provincia option')
        .hide() // hide all
        .filter('[value="'+$(this).val()+'"]') // filter options with required value
            .show(); // and show them
});*/

$('#provincia').change(function(){
    $('#isla option')
        .hide() // hide all
        .filter('[value="'+$(this).val()+'"]') // filter options with required value
            .show(); // and show them
});

$('#isla').change(function(){
    $('#municipio option')
        .hide() // hide all
        .filter('[value="'+$(this).val()+'"]') // filter options with required value
            .show(); // and show them
});

$('#isla').change(function(){
    $('#municipio option')
        .hide() // hide all
        .filter('[value="'+$(this).val()+'"]') // filter options with required value
            .show(); // and show them
});