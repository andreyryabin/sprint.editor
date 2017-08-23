jQuery(document).ready(function ($) {
    $('.sp-video-gallery').sprintVideoGallery({
        thumbnailsNavButtons: false,
        thumbnailsVisible: {
            0: 3,
            640: 4,
            1280: 5
        }
    });
});