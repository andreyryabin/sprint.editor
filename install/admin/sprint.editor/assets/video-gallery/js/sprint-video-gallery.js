;(function ($) {
    'use strict';

    $.fn.sprintVideoGallery = function (options) {

        var defaults = {

            thumbnailsVisible: {
                0: 3
            },

            thumbnailsNavButtons: true

        };

        defaults = $.extend(defaults, options);

        function SprintVideoGallery($container, settings) {
            this.$container = $container;
            this.settings = settings;
            this.classPrefix = 'sp-video-gallery';
            this.thumbnails = [];
            this.thumbnailActiveIndex = 1;
            this.thumbnailsScrollLeft = 0;
            this.visibleThumbnails = [];

            this.thumbnailsScrollCursorwidth = 10;
            this.hasNiceScroll = ($.fn.niceScroll);

            this.setupHtml();
            this.addListeners();
        }

        SprintVideoGallery.prototype = {

            constructor: SprintVideoGallery,

            setupHtml: function () {
                this.setupPreloader();
                this.setupStructure();

                if (this.settings.thumbnailsNavButtons) {
                    this.setupThumbnailsNavButtons();
                }

                this.setupThumbnailsSize();
                this.setupThumbnailsScroll();
            },

            setupStructure: function () {

                this.$container.addClass(this.classPrefix);

                this.$largeThumbnailContainer = $('<div>', {
                    'class': this.classPrefix + '-large-thumbnail-container'
                });

                this.$largeThumbnailWrapper = $('<div>', {
                    'class': this.classPrefix + '-large-thumbnail-wrapper'
                });

                this.$thumbnailsContainer = $('<div>', {
                    'class': this.classPrefix + '-thumbnails-container'
                });

                this.$thumbnailsListContainer = $('<div>', {
                    'class': this.classPrefix + '-thumbnails-list-container'
                });

                this.$thumbnailsList = $('<ul>', {
                    'class': this.classPrefix + '-thumbnails-list'
                });

                this.$container.find('> div').each(function (i, $thumbnail) {
                    if ($thumbnail === this.$preloader[0]) return;
                    var $li = $('<li>', {
                        'class': this.classPrefix + '-thumbnails-list-item'
                    }).appendTo(this.$thumbnailsList);

                    $thumbnail = $($thumbnail);

                    $thumbnail.attr('data-id', i + 1);

                    $li.append($thumbnail);
                    this.thumbnails.push($li);

                    if (i === 0) {
                        this.setActiveThumbnail($li);
                        this.setLargeThumbnail($thumbnail, false);
                    }

                }.bind(this));

                this.$largeThumbnailContainer.append(this.$largeThumbnailWrapper);

                this.$container.append(
                    this.$largeThumbnailContainer,
                    this.$thumbnailsContainer
                );

                this.$thumbnailsListContainer.append(this.$thumbnailsList);
                this.$thumbnailsContainer.append(this.$thumbnailsListContainer);
            },

            setLargeThumbnail: function ($thumbnail, onPreloader) {
                var src = $thumbnail.attr('data-src'),
                    largeThumbnail,
                    type, alt;


                type = $thumbnail.attr('data-type');

                if (type === 'image') {
                    alt = $thumbnail.find('img').attr('alt');
                    largeThumbnail = '<img src="' + src + '" alt="' + alt + '">';
                }

                if (type === 'youtube') {
                    largeThumbnail = '<iframe type="text/html" src="https://www.youtube.com/embed/' + src + '" allowfullscreen></iframe>';
                }


                if (largeThumbnail) {
                    this.showPreloader(this.$largeThumbnailWrapper);

                    this.$largeThumbnailWrapper.empty().html(largeThumbnail);

                    if (type === 'image') {
                        let $image = this.$largeThumbnailWrapper.find('img')[0];
                        if ($image) {
                            $image.onload = function () {
                                this.hidePreloader();
                            }.bind(this);
                        }
                    }

                    if (type === 'youtube') {
                        this.hidePreloader();
                    }
                }

            },

            setupPreloader: function () {
                this.$preloader = $('<div>', {
                    'class': this.classPrefix + '-preloader'
                }).appendTo(this.$container);
            },

            showPreloader: function ($elem) {
                var elemCoords = $elem.offset();
                this.$preloader.show();

                this.$preloader.offset({
                    left: elemCoords.left,
                    top: elemCoords.top
                });

                this.$preloader.css({
                    'height': $elem.outerHeight(),
                    'width': $elem.outerWidth()
                });

                this.$preloader.css('opacity', 1);
            },

            hidePreloader: function ($elem) {
                this.$preloader.hide();
                this.$preloader.css('opacity', '');
            },

            setupThumbnailsSize: function () {
                var self = this,
                    width = this.$thumbnailsListContainer.outerWidth(),
                    screen,
                    thumbnailsImgHeight = 0;

                if (self.settings.thumbnailsNavButtons) {
                    width = width -
                        (self.thumbnailsControls.prev.outerWidth() + self.thumbnailsControls.next.outerWidth());
                    self.$thumbnailsListContainer.css('width', width);
                    self.$thumbnailsListContainer.css('margin-left', self.thumbnailsControls.prev.outerWidth());
                }

                for (screen in self.settings.thumbnailsVisible) {
                    if (this.$container.outerWidth() >= screen) {
                        self.thumbnailWidth = width / self.settings.thumbnailsVisible[screen];
                        self.thumbnailsGroupCount = self.settings.thumbnailsVisible[screen];
                    }
                }

                self.thumbnailsGroupWidth = self.thumbnailsGroupCount * self.thumbnailWidth;

                $(self.thumbnails).each(function (index) {
                    var $this = $(this),
                        $img = $this.find('img');

                    $this.outerWidth(self.thumbnailWidth);

                    if (index < self.thumbnailsGroupCount) {
                        $this.addClass('visible');
                        self.visibleThumbnails.push($this);
                    }
                });

                self.$largeThumbnailContainer.css('height', this.$container.outerWidth() / 100 * 56.5);

                if (!self.hasNiceScroll) {
                    self.$thumbnailsList.css('width', '100%');
                } else {
                    self.$thumbnailsList.css('width', self.thumbnailWidth * self.thumbnails.length);
                }

                self.$thumbnailsList.css('height', self.thumbnailWidth / 100 * 56.5);
                self.$thumbnailsListContainer.css('height', self.thumbnailWidth / 100 * 56.5 + self.thumbnailsScrollCursorwidth);
            },

            setupThumbnailsNavButtons: function () {
                this.thumbnailsControls = {
                    prev: $('<div>', {
                        'class': this.classPrefix + '-thumbnails-prev'
                    }).appendTo(this.$thumbnailsContainer),

                    next: $('<div>', {
                        'class': this.classPrefix + '-thumbnails-next'
                    }).appendTo(this.$thumbnailsContainer)
                };
            },

            setupThumbnailsScroll: function () {
                if (this.hasNiceScroll) {
                    this.$thumbnailsListContainer.niceScroll('.sp-video-gallery-thumbnails-list', {
                        background: 'rgba(0,0,0,.2)',
                        cursorborder: 'none',
                        cursorcolor: '#2b77c6',
                        cursorwidth: this.thumbnailsScrollCursorwidth,
                        autohidemode: false,
                        cursorborderradius: 0
                    });

                    this.thumbnailsScroll = this.$thumbnailsListContainer.getNiceScroll(0);
                }
            },

            setActiveThumbnail: function ($item) {
                this.activeThumbnail = $item;
                $item.addClass('active');
            },

            removeActiveThumbnail: function ($item) {
                $item.removeClass('active');
            },

            addListeners: function () {
                $(window).on('resize', this.setupThumbnailsSize.bind(this));

                if (this.settings.thumbnailsNavButtons) {
                    this.thumbnailsControls.prev.on('click', this.thumbnailsSlidePrev.bind(this));
                    this.thumbnailsControls.next.on('click', this.thumbnailsSlideNext.bind(this));
                }

                this.$thumbnailsList.on('click', this.thumbnailsClick.bind(this));
            },

            thumbnailsSlidePrev: function () {
                var $prev = this.activeThumbnail.prev(),
                    remainingLength = this.thumbnails.length - this.thumbnailActiveIndex,
                    $next,
                    i;

                if (this.thumbnailActiveIndex === 1) {

                    $.each(this.visibleThumbnails, function () {
                        this.removeClass('visible');
                    });

                    this.visibleThumbnails = [];

                    for (i = 0; i < this.thumbnails.length; i++) {
                        if (i >= this.thumbnails.length - this.thumbnailsGroupCount) {
                            this.thumbnails[i].addClass('visible');
                            this.visibleThumbnails.push(this.thumbnails[i]);
                        }
                    }

                    this.thumbnailsScrollLeft = this.thumbnailWidth * this.thumbnails.length - this.thumbnailsGroupWidth;
                    this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);
                    this.thumbnailActiveIndex = this.thumbnails.length;
                    this.removeActiveThumbnail(this.activeThumbnail);
                    this.setActiveThumbnail(this.thumbnails[this.thumbnails.length - 1]);
                    this.setLargeThumbnail(this.activeThumbnail.find('> div'));
                    return;
                }

                if (!$prev.hasClass('visible')) {

                    $.each(this.visibleThumbnails, function () {
                        this.removeClass('visible');
                    });

                    this.visibleThumbnails = [];

                    if (this.thumbnailActiveIndex >= this.thumbnailsGroupCount) {

                        for (i = 0; i < this.thumbnailsGroupCount; i++) {
                            if (i !== 0) $prev = $prev.prev();
                            $prev.addClass('visible');
                            this.visibleThumbnails.push($prev);
                        }
                        this.thumbnailsScrollLeft -= this.thumbnailsGroupWidth;
                        this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);

                    } else {

                        if (remainingLength < this.thumbnailsGroupCount) {

                            for (i = 0; i < this.thumbnailsGroupCount - remainingLength; i++) {
                                if (i === 0) {
                                    $next = $prev.next();
                                } else {
                                    $next = $next.next();
                                }

                                $next.addClass('visible');
                                this.visibleThumbnails.unshift($next);
                            }

                        }

                        for (i = 0; i < remainingLength; i++) {
                            if (i !== 0) $prev = $prev.prev();
                            $prev.addClass('visible');
                            this.visibleThumbnails.push($prev);
                        }

                        this.thumbnailsScrollLeft -= this.thumbnailWidth * remainingLength;
                        this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);

                    }

                } else {
                    this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);
                }

                this.removeActiveThumbnail(this.activeThumbnail);
                this.setActiveThumbnail(this.activeThumbnail.prev());
                this.thumbnailActiveIndex--;
                this.setLargeThumbnail(this.activeThumbnail.find('> div'));
            },

            thumbnailsSlideNext: function () {
                var $next = this.activeThumbnail.next(),
                    remainingLength = this.thumbnails.length - this.thumbnailActiveIndex,
                    $prev,
                    i;

                if (this.thumbnailActiveIndex === this.thumbnails.length) {

                    $.each(this.visibleThumbnails, function () {
                        this.removeClass('visible');
                    });

                    this.visibleThumbnails = [];

                    for (i = 0; i < this.thumbnailsGroupCount; i++) {
                        this.thumbnails[i].addClass('visible');
                        this.visibleThumbnails.push(this.thumbnails[i]);
                    }

                    this.thumbnailsScrollLeft = 0;
                    this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);
                    this.thumbnailActiveIndex = 1;
                    this.removeActiveThumbnail(this.activeThumbnail);
                    this.setActiveThumbnail(this.thumbnails[0]);
                    this.setLargeThumbnail(this.activeThumbnail.find('> div'));
                    return;
                }

                if (!$next.hasClass('visible')) {

                    $.each(this.visibleThumbnails, function () {
                        this.removeClass('visible');
                    });

                    this.visibleThumbnails = [];

                    if (remainingLength >= this.thumbnailsGroupCount) {

                        for (i = 0; i < this.thumbnailsGroupCount; i++) {
                            if (i !== 0) $next = $next.next();
                            $next.addClass('visible');
                            this.visibleThumbnails.push($next);
                        }

                        this.thumbnailsScrollLeft += this.thumbnailsGroupWidth;
                        this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);

                    } else {

                        if (remainingLength < this.thumbnailsGroupCount) {

                            for (i = 0; i < this.thumbnailsGroupCount - remainingLength; i++) {
                                if (i === 0) {
                                    $prev = $next.prev();
                                } else {
                                    $prev = $prev.prev();
                                }

                                $prev.addClass('visible');
                                this.visibleThumbnails.unshift($prev);
                            }

                        }

                        for (i = 0; i < remainingLength; i++) {
                            if (i !== 0) $next = $next.next();
                            $next.addClass('visible');
                            this.visibleThumbnails.push($next);
                        }

                        this.thumbnailsScrollLeft += this.thumbnailWidth * remainingLength;
                        this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);

                    }

                } else {
                    this.thumbnailsScroll.doScrollLeft(this.thumbnailsScrollLeft, 200);
                }

                this.removeActiveThumbnail(this.activeThumbnail);
                this.setActiveThumbnail(this.activeThumbnail.next());
                this.thumbnailActiveIndex++;

                this.setLargeThumbnail(this.activeThumbnail.find('> div'));
            },

            thumbnailsClick: function (e) {
                var $target = $(e.target),
                    $selected = $target.closest('.sp-video-gallery-thumbnails-list-item'),
                    self = this;

                if ($selected.length) {

                    self.removeActiveThumbnail(self.activeThumbnail);
                    self.setActiveThumbnail($selected);

                    $.each(self.thumbnails, function (index) {
                        if (this[0] === $selected[0]) {
                            self.thumbnailActiveIndex = ++index;
                        }
                    });

                    self.setLargeThumbnail($selected.find('> *'));
                }
            },

        };

        this.each(function () {
            new SprintVideoGallery($(this), defaults);
        });

        return this;

    };

})(jQuery);
