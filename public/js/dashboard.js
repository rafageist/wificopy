// Name: 	jQuery --> Leopard

$(document).ready(function(){
    //launch dock
    $('#dock').jqDock();
    //draggables defenition
    $('.widget').draggable();
    $('.draggableWindow').draggable({
        handle: 'h1'
    });

    $('#divrojo').hide();
        
    
    //initial hiding of dashboard + addition of 'closeZone'
$('#dashboardWrapper')
	.css({
		position: 'absolute',
		top: '0px',
		left: '0px',
		width: '100%',
		height: '100%'/*,
		opacity: '0'*/
	})
	.hide()
	.append('<div id="closeZone"></div>');
    
    //Position, and hiding of '#closeZone'.
$('#closeZone')
	.css({
		position: 'absolute',
		top: '0px',
		left: '0px',
		width: '100%',
		height: '100%',
		opacity: '0.5',
		background: '#000'
	});
    
    //Launch Dashboard + initiation of 'closeZone'
$('#dashboardLaunch').click(function(){
	/*$('#dashboardWrapper')
		.show()
		.animate({opacity: '1'}, 300);
      */  
    $('#divrojo').show('fast');
        
});

//closeZone's job: escaping the Dashboard
$('#closeZone').click(function(){
	$('#dashboardWrapper')
		.animate({opacity: '0'}, 300)
		.hide(1);
    });
    
    //fadeout of dashboard and when a link is clicked within
$('#dashboardWrapper a').click(function(){
	$('#dashboardWrapper').show();/*({opacity: '0'}, 300);*/
});

});
