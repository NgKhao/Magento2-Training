define([
    'uiComponent',
    'ko',
    'jquery'
], function (Component, ko, $) {
    'use strict';

    return Component.extend({
        defaults: {
            availableColors: [], // Mảng các màu có sẵn
            selectedColor: 'default' // Màu được chọn, mặc định là 'default'
        },

        initialize: function () {
            this._super();

            // Chuyển selectedColor thành observable sau khi initialize
            this.selectedColor = ko.observable(this.selectedColor);

            var self = this;

            // Lắng nghe thay đổi của selectedColor
            this.selectedColor.subscribe(function (newValue) {
                self.changeBodyBackground(newValue);
            });
        },

        changeBodyBackground: function (colorCode) {
            var body = $('body');
            if(colorCode === 'default'){
                body.css('background-color', '');
            } else
            {
                body.css('background-color', colorCode);
            }
        }
    });
})
