$(document).ready(function(){
	if(document.getElementById("pegajoso1"))
	{
		var altura = $('.menu').offset().top;
//alert(altura);
$(window).on('scroll',function(){

	if($(window).scrollTop()>altura && screen.width >1024)
	{
		$('.menu').addClass('menu-fixed');//Añadimos una clase donde esta el .menu
	}else{
		$('.menu').removeClass('menu-fixed');
	}
})
}

else if($(document).width() > 767)
{
	$('.menu').addClass('menu-nofixed');
}
});

