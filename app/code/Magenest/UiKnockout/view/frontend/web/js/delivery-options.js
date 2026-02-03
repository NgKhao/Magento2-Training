define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/calendar' // Thư viện Datepicker của Magento
], function (Component, ko, $) {
    'use strict';

    return Component.extend({
        /**
         * defaults: Khai báo các thuộc tính mặc định, KHÔNG khai báo ko.observable() trong defaults!
         */
        defaults: {
            targetInputName: 'options[4]' // Tên của input ẩn sẽ cập nhật giá trị
        },

        // this ở đây là Component
        initialize: function () {
            this._super(); // Kế thừa logic của cha
            console.log('Delivery Options Component đã khởi tạo!');
            console.log('Target Input:', this.targetInputName);

            // Khởi tạo các Observable
            // Observable = Biến có thể "lắng nghe" được (reactive)
            this.deliveryType = ko.observable('today'); // Mặc định: Giao trong ngày
            this.selectedDate = ko.observable('');      // Ngày được chọn (ban đầu rỗng)
            this.finalValue = ko.observable('Giao hàng trong ngày'); // Giá trị cuối cùng gửi lên server

            // BƯỚC 2: Đăng ký lắng nghe thay đổi (Subscribe)
            var self = this;

            // this ở đây là component nhưng khi vào trong function thì this sẽ là function đó,
            // vì vậy nếu không có self thì không truy cập được components
            // Subscribe 1: Khi finalValue thay đổi -> input ẩn sẽ update theo
            this.finalValue.subscribe(function (newValue) {
                console.log('📝 finalValue changed:', newValue);
                self.updateNativeInput(newValue);
            });

            // Subscribe 2: Khi chọn ngày từ datepicker → Cập nhật finalValue
            this.selectedDate.subscribe(function (date) {
                console.log('📅 selectedDate changed:', date);
                if (self.deliveryType() === 'custom' && date) {
                    self.finalValue("Giao ngày: " + date);
                }
            });

            // BƯỚC 3: Cập nhật input ẩn ngay lần đầu
            this.updateNativeInput(this.finalValue());
        },

        /**
         * Hàm xử lý khi chuyển đổi radio button
         * @param {string} type - 'today' hoặc 'custom'
         */
        selectType: function (type) {
            console.log('🔘 Radio button changed to:', type);

            this.deliveryType(type); // Update observable

            if (type === 'today') {
                // Chọn giao hàng trong ngày
                this.finalValue('Giao hàng trong ngày');
                this.selectedDate(''); // Reset ngày đã chọn
            } else {
                // Chọn ngày tùy chỉnh
                if (this.selectedDate()) {
                    // Nếu đã chọn ngày trước đó → Giữ nguyên
                    this.finalValue("Giao ngày: " + this.selectedDate());
                } else {
                    // Chưa chọn → Bắt buộc phải chọn
                    this.finalValue(''); // Để trống, bắt buộc khách phải chọn ngày
                }
            }

            return true; // Phải return true để radio button hoạt động bình thường
        },

        /**
         * Cập nhật giá trị vào input ẩn của Magento
         * Input này sẽ được gửi lên server khi Add to Cart
         * @param {string} value - Giá trị cần cập nhật
         */
        updateNativeInput: function (value) {
            var inputSelector = '[name="' + this.targetInputName + '"]'; // Selector cho input ẩn
            var $input = $(inputSelector); // Tìm input ẩn theo tên

            if ($input.length) {
                $input.val(value).trigger('change'); // trigger 'change' để Magento validation biết
                console.log('✅ Đã update input:', inputSelector, '=', value);
            } else {
                console.error('❌ Không tìm thấy input:', inputSelector);
                console.log('Kiểm tra xem input có được render trong template không?');
            }
        },

        /**
         * Cấu hình cho jQuery UI Datepicker
         * @return {object} Options object
         */
        getDatePickerOptions: function() {
            return {
                dateFormat: 'dd/mm/yy', // Định dạng ngày: 28/01/2026
                minDate: 0,             // Không cho chọn ngày quá khứ (0 = hôm nay)
                showsTime: false,       // Không hiển thị giờ
                buttonText: 'Chọn ngày' // Text cho button (nếu có)
            };
        }
    });
});
