/**
 * JS Component để xử lý toggle giữa Link URL và File Upload
 * dựa trên giá trị của Type field
 *
 * Logic:
 * - Khi type = 'link': hiển thị Link URL, ẩn File Upload
 * - Khi type = 'file': ẩn Link URL, hiển thị File Upload
 *
 * Tham khảo: Magento_Downloadable/js/components/upload-type-handler.js
 */
define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            listens: {
                value: 'onTypeChange'
            },
            // Cấu hình tên field link_url và file
            typeUrl: 'link_url',      // index của field Link URL
            typeFile: 'file',          // index của field File Upload
            // Filter để tìm các component cùng parent
            filterPlaceholder: 'ns = ${ $.ns }, parentScope = ${ $.parentScope }'
        },

        /**
         * Initialize component
         * Gọi onTypeChange với giá trị ban đầu để set visibility đúng
         */
        initialize: function () {
            return this
                ._super()
                .onTypeChange(this.initialValue);
        },

        /**
         * Callback khi value thay đổi
         * @param {String} currentValue
         */
        onUpdate: function (currentValue) {
            this.onTypeChange(currentValue);
            return this._super();
        },

        /**
         * Xử lý thay đổi type
         * @param {String} currentValue - 'link' hoặc 'file'
         */
        onTypeChange: function (currentValue) {
            var componentUrl = this.filterPlaceholder + ', index=' + this.typeUrl,
                componentFile = this.filterPlaceholder + ', index=' + this.typeFile;

            switch (currentValue) {
                case 'link':
                    // Hiển thị Link URL, ẩn File Upload
                    this.setVisible(componentUrl, true);
                    this.setVisible(componentFile, false);
                    break;

                case 'file':
                    // Ẩn Link URL, hiển thị File Upload
                    this.setVisible(componentUrl, false);
                    this.setVisible(componentFile, true);
                    break;

                default:
                    // Mặc định hiển thị cả 2
                    this.setVisible(componentUrl, true);
                    this.setVisible(componentFile, true);
                    break;
            }
        },

        /**
         * Set visibility cho component
         * @param {String} filter - Registry filter để tìm component
         * @param {Boolean} visible
         */
        setVisible: function (filter, visible) {
            registry.async(filter)(
                function (component) {
                    component.visible(visible);
                }
            );
        }
    });
});

