/**
 * Translate plugin for Craft CMS 3.x
 *
 * Translation management plugin for Craft CMS
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2017 Statik.be
 */

(function($)
{
    /**
     * StatikTranslate class
     */
    var StatikTranslate = Garnish.Base.extend({

        options: null,
        $toHeader:null,
        $menu: null,
        $form: null,

        /**
         * The constructor.
         */
        init: function()
        {
            this.addListener($('#save-elements-button'), 'activate', 'processAjaxCall');
            this.$form = $("#translate-ajax");
            var settings = {};
            this.$menu = new Garnish.MenuBtn("#statik-menubtn", settings);
            var $siteMenu = $('.sitemenubtn:first').menubtn().data('menubtn').menu;
            var $siteIdInput = $('input[name="siteId"]');

            // Upload file on click
            $('.translations-upload-button').click(function() {
                $('input[name="translations-upload"]').click().change(function() {
                    $(this).parent('form').submit();
                });
            });

            // Init the form
            let siteId = this.getSiteId();
            $siteIdInput.val(siteId);

            // Change the siteId when on hidden values
            $siteMenu.on('optionselect', function(ev) {
                $siteIdInput.val($(ev.selectedOption).data('siteId'));
            });

            Craft.elementIndex.on('afterAction', this.manageAfterAction);
            this.$menu.on('optionSelect', this.manageMenu)
        },

        getSiteId: function() {
            // If the old BaseElementIndex.siteId value is in localStorage, go aheand and remove & return that
            let siteId = Craft.getLocalStorage('BaseElementIndex.siteId');
            if (typeof siteId !== 'undefined') {
                Craft.removeLocalStorage('BaseElementIndex.siteId');
                this.setSiteId(siteId);
                return siteId;
            }
            return Craft.getCookie('siteId');
        },

        manageMenu: function(event)
        {
            var data = {
                siteId: Craft.elementIndex.siteId,
                sourceKey: Craft.elementIndex.sourceKey
            };

            Craft.postActionRequest('translate/translate/download', data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    if (response.success)
                    {
                        if (response.filePath){
                            var $iframe = $('<iframe/>', {'src': Craft.getActionUrl('translate/translate/download-csv-file', {'filepath': response.filePath})}).hide();
                            $("#translate-ajax").append($iframe);
                            Craft.cp.displayNotice(Craft.t('translate', 'Downloading file'));
                        }
                        else {
                            Craft.cp.displayError(Craft.t('app', 'There was an error when generating the file'));
                        }
                    }
                    else {
                        Craft.cp.displayError(Craft.t('app', 'Please select a different source'));
                    }
                }
                else {
                    Craft.cp.displayError(Craft.t('app', 'An unknown error occurred.'));
                }
            }, this));
        },

        manageAfterAction: function(action, params)
        {
            Craft.elementIndex.updateElements();
        },

        processAjaxCall: function(event)
        {
            $('.elements').hide();
            $('.flex .spinner').removeClass('invisible');
            event.preventDefault();
            var data = this.$form.serializeArray();
            data.push({name: 'sourceKey', value: Craft.elementIndex.sourceKey});
            Craft.postActionRequest('translate/translate/save', data, $.proxy(function (response, textStatus) {
                if (textStatus === 'success') {
                    if (response.success) {
                        setTimeout(function () {
                            Craft.cp.displayNotice(Craft.t('translate', 'Translations saved'));
                            Craft.elementIndex.updateElements();
                            $('.elements').show();
                        }, 3000);
                    }
                } else {
                    Craft.cp.displayError(Craft.t('app', 'An unknown error occurred.'));
                }
            }, this));
        }
    });

    window.StatikTranslate = StatikTranslate;

})(jQuery);