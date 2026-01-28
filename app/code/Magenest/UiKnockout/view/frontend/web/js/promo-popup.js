define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/customer-data', // Core 1: Quản lý dữ liệu khách hàng (Private Content)
    'Magento_Ui/js/modal/modal'          // Core 2: Thư viện Popup chuẩn của Magento
], function ($, Component, ko, customerData, modal) {
    'use strict';

    return Component.extend({
        // Khai báo các biến mặc định
        defaults: {
            configData: {} // Biến này sẽ dữ liệu jsonsofig từ PHTML truyền sang
        },

        // Hàm initialize chạy đầu tiên khi Component được sinh ra
        initialize: function () {
            this._super(); // Luôn gọi cái này để kế thừa cha

            console.log('Component đã chạy!');
            console.log('Config nhận được:', this.configData);

            //Nếu Admin tắt chức năng -> Dừng luôn.
            if (!this.configData.is_enabled) {
                return;
            }

            // Logic 2: Lắng nghe dữ liệu khách hàng (Customer Data)
            // Tại sao? Vì Magento Cache trang HTML. Ta không biết ai đang login.
            // Phải chờ JS load từ LocalStorage trình duyệt ra.

            var self = this; // Giữ tham chiếu đến this của Component
            this.customer = customerData.get('customer'); // Lấy section 'customer' trong localStorage

            // Đăng ký theo dõi: Khi dữ liệu customer load xong hoặc thay đổi -> Chạy hàm check
            this.subscription = this.customer.subscribe(function (data) {
                self.checkAndShowPopup(data);
            });

            // Chạy thử 1 lần ngay lập tức (phòng trường hợp data đã có sẵn)
            this.checkAndShowPopup(this.customer());
        },

        // Hàm kiểm tra điều kiện logic
        checkAndShowPopup: function (customerInfo) {
            // Bước 1: Check xem có dữ liệu group_id chưa (Do Plugin ở Bước 2 thêm vào)
            if (!customerInfo || !customerInfo.group_id) {
                console.log('Chưa có group_id, thoát!');
                return;
            }

            var currentGroupId = customerInfo.group_id.toString();
            var allowedGroups = this.configData.target_groups;

            console.log('User Group:', currentGroupId);
            console.log('Allowed Groups:', allowedGroups);

            // Bước 2: User hiện tại có nằm trong nhóm cho phép không?
            // Dùng hàm indexOf của mảng JS để check
            if (allowedGroups.indexOf(currentGroupId) === -1) {
                return; // Không thuộc nhóm -> Thoát
            }
            this.openPopup();
        },

        // Hàm xử lý giao diện (UI)
        openPopup: function () {
            // FIX LỖI: Đợi KnockoutJS render template xong
            // Tại sao? Vì flow như sau:
            // 1. Component initialize() chạy
            // 2. CustomerData trigger → checkAndShowPopup() → openPopup()
            // 3. NHƯNG KnockoutJS chưa render template HTML ra DOM!
            // 4. → Không tìm thấy #popup-content-html

            // Giải pháp: Dùng setTimeout để delay 1 chút, đợi KnockoutJS render xong

            setTimeout(function() {
                console.log('Bắt đầu tìm element sau khi delay...');

                var element = $('#popup-content-html');

                if (element.length) {
                    console.log('Tìm thấy element, khởi tạo modal...');

                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: 'Thông báo ưu đãi',
                        buttons: [{
                            text: 'Đóng lại',
                            class: '',
                            click: function () {
                                this.closeModal();
                            }
                        }],
                        closed: function () {
                            // localStorage.setItem('da_hien_popup_uu_dai', 'true');
                            // Nếu muốn chỉ hiện 1 lần trong session, uncomment dòng dưới:
                            // sessionStorage.setItem('da_hien_popup_uu_dai', 'true');
                        }
                    };

                    // Kiểm tra xem modal đã được khởi tạo chưa
                    // Nếu chưa → Khởi tạo lần đầu
                    // Nếu rồi → Chỉ cần mở lại
                    if (!element.data('mage-modal')) {
                        modal(options, element); // Biến thẻ div thành Modal (chỉ làm 1 lần)
                    }

                    element.modal('openModal'); // Mở popup lên
                } else {
                    console.error('Không tìm thấy element #popup-content-html');
                    console.log('Có thể do:');
                    console.log('1. File .phtml chưa render đúng');
                    console.log('2. Layout XML chưa được apply');
                    console.log('3. Cache chưa được xóa');
                    console.log('4. KnockoutJS template chưa được render');
                }
            }, 500); // Đợi 500ms cho KnockoutJS render xong
        }
    });
});
