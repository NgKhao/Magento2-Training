define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
] , function($, modal, alert) {
    'use strict';

    /**
    * Return function để Magento gọi khi khởi tạo
    * 
    * @param {Object} config - Options từ data-mage-init hoặc x-magento-init
    * @param {HTMLElement} element - Element được bind (nếu có)
    */
   return function(config, element){
        $('#btn-alert-modal').on('click', function(){
            alert({
                title: $.mage.__('Hello World!'),
                content: $.mage.__('This is an alert modal example.'),
                modalClass: 'alert-modal-custom',
                buttons:[{
                    text: $.mage.__('Ok'),
                    class: 'action primary accept', // use class Magento co san
                    click: function() {
                        this.closeModal();
                    }
                }]
            })
            
        })

        var modalOptions = {
            type: 'popup', // Loại modal: popup 
            title: $.mage.__('Login Modal'), // Tiêu đề modal
            buttons: [{
                text: $.mage.__('Close'),
                class: 'action secondary',
                click: function () {
                    this.closeModal();
                }
            }],
        }

        var loginModal = $('#login-modal-content').modal(modalOptions); //khởi tạo modal
        $('#btn-login-modal').on('click', function(){
            loginModal.modal('openModal'); // Mở modal khi click nút
        })
    }
})