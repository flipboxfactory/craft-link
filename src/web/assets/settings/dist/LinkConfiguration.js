(function ($) {
    /**
 * global: Craft 
*/
    /**
 * global: Garnish 
*/
    Craft.LinkTypeManager = Garnish.Base.extend(
        {
            $typeSelect: null,
            $spinner: null,
            $typesContainer: null,
            $types: null,
            $nav: null,
            namespace: null,

            $selectedTab: null,

            init: function ($typeSelect, $typesContainer, namespace) {
                this.$typeSelect = $typeSelect;
                this.$spinner = $('<div class="spinner hidden"/>').insertAfter(this.$typeSelect.parent());
                this.$typesContainer = $typesContainer;
                this.$types = $typesContainer.children('.types');
                this.$nav = $typesContainer.children('.tabs').children('ul');
                this.namespace = namespace;

                this.initTabs()

                // Load existing
                $.each(
                    this.$nav.children(), $.proxy(
                        function (index, value) {
                            var $nav = $(value);
                            var $link = $nav.children('a');
                            var id = $link.attr('href');

                            var LinkType = new Craft.LinkType(this);

                            LinkType.setHtml($(id));
                            LinkType.$nav = $(value);
                        }, this
                    )
                );

                this.addListener(this.$typeSelect, 'change', 'onTypeChange');
            },

            initTabs: function() {
                this.$selectedTab = null;

                var $tabs = this.$nav.find('> li');
                var tabs = [];
                var tabWidths = [];
                var totalWidth = 0;
                var i, a, href;

                for (i = 0; i < $tabs.length; i++) {
                    tabs[i] = $($tabs[i]);
                    tabWidths[i] = tabs[i].width();
                    totalWidth += tabWidths[i];

                    // Does it link to an anchor?
                    a = tabs[i].children('a');
                    href = a.attr('href');
                    if (href && href.charAt(0) === '#') {
                        this.addListener(
                            a, 'click', function(ev) {
                                ev.preventDefault();
                                this.selectTab(ev.currentTarget);
                            }
                        );

                        if (href === document.location.hash) {
                            this.selectTab(a);
                        }
                    }

                    if (!this.$selectedTab && a.hasClass('sel')) {
                        this.$selectedTab = a;
                    }
                }

                // Now set their max widths
                for (i = 0; i < $tabs.length; i++) {
                    tabs[i].css('max-width', (100 * tabWidths[i] / totalWidth) + '%');
                }
            },

            selectTab: function(tab) {
                var $tab = $(tab);

                if (this.$selectedTab) {
                    if (this.$selectedTab.get(0) === $tab.get(0)) {
                        return;
                    }
                    this.deselectTab();
                }

                $tab.addClass('sel');
                var href = $tab.attr('href')
                $(href).removeClass('hidden');
                if (typeof history !== 'undefined') {
                    history.replaceState(undefined, undefined, href);
                }
                Garnish.$win.trigger('resize');
                // Fixes Redactor fixed toolbars on previously hidden panes
                Garnish.$doc.trigger('scroll');
                this.$selectedTab = $tab;
            },

            deselectTab: function() {
                if (!this.$selectedTab) {
                    return;
                }

                this.$selectedTab.removeClass('sel');
                if (this.$selectedTab.attr('href').charAt(0) === '#') {
                    $(this.$selectedTab.attr('href')).addClass('hidden');
                }
                this.$selectedTab = null;
            },

            getCount: function () {
                return this.$nav.children().length
            },

            onTypeChange: function (ev) {
                this.$spinner.removeClass('hidden');

                var val = this.$typeSelect.val();

                if (!val) {
                    return;
                }
                var data = {
                    fieldId: this.fieldId,
                    type: this.$typeSelect.val(),
                    namespace: this.namespace
                };

                Craft.postActionRequest(
                    'link/type/settings', data, $.proxy(
                        function (response, textStatus) {
                            this.$spinner.addClass('hidden');

                            if (textStatus == 'success') {
                                this.appendType(
                                    new Craft.LinkType(
                                        this,
                                        response.label,
                                        response.paneHtml
                                    )
                                );

                                Craft.appendHeadHtml(response.headHtml);
                                Craft.appendFootHtml(response.footHtml);
                            }
                        }, this
                    )
                );
            },
            
            appendType: function (LinkType) {
                // Append new html and nav
                this.$types.append(LinkType.$html);
                this.$nav.append(LinkType.$nav);

                // refresh
                this.refresh();

                // Select tab
                this.selectTab(LinkType.$nav.children('a'))
            },

            selectFirstTab: function() {
                if (this.$nav.children().length <= 0) {
                    return;
                }

                this.selectTab($(this.$nav.children()[0]).children('a'))
            },
            
            refresh: function () {
                if (this.$nav.children().length <= 0) {
                    this.$typesContainer.hide();
                    return;
                } else {
                    this.$typesContainer.show();
                }

                Craft.initUiElements(this.$typesContainer);
                this.initTabs();
            }
        }
    );

    Craft.LinkType = Garnish.Base.extend(
        {
            manager: null,
            id: null,
            label: null,
            $html: null,
            $nav: null,
            $link: null,
            $remove: null,

            init: function (manager, label, html, id) {

                this.manager = manager;
                this.id = id ? id : Math.random().toString(36).substr(2, 5);
                this.label = label;

                if (html) {
                    this.setHtml(
                        $(
                            '<div/>', {
                                class: 'type',
                                id: this.id
                            }
                        ).html(html)
                    );
                }

                if (label) {
                    this.$link = $(
                        '<a/>', {
                            text: this.label,
                            class: 'tab',
                            href: '#' + this.id
                        }
                    );
                    this.$nav = $('<li/>').html(this.$link);
                }

            },
            setHtml: function ($html) {
                this.$html = $html;
                this.$remove = this.$html.find('.remove');
                if (this.$remove.length) {
                    this.addListener(this.$remove, 'click', 'onRemove');
                }
            },
            onRemove: function (e) {
                e.preventDefault();
                this.$html.remove();
                this.$nav.remove();
                this.manager.refresh();
                this.manager.selectFirstTab();
                this.destroy();
            }
        }
    );
})(jQuery);