
  jQuery(document).ready(function($) {

    $('.mwf_photos .mwf_photo').hide();

    $('html').removeClass('no-js').addClass('js');

    var $container = $('.mwf_photos');
    
    $container.imagesLoaded(function(){
      $container.find('.mwf_photo').fadeIn('fast'); // Fade back in the thumbnails when the layout is complete
      $container.masonry({
        itemSelector : '.mwf_photo', 
        isAnimated : true, // Animate the layout when changing window size
        columnWidth: 260, // Width of the thumbnail including any padding and borders
        gutterWidth: 10 // The gap between thumbnails
      });
      $container.removeClass('loading'); // Remove the loading class from the container
    });
    
    $(".fancybox").fancybox({
      openEffect  : 'elastic',
      closeEffect : 'elastic',
      padding : 10,
      helpers : {
        title : {
          type : 'inside'
        }
      }
    });

});
