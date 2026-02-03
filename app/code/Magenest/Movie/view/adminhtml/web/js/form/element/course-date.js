define([
    'Magento_Ui/js/form/element/date'
], function (DateElement) {
    'use strict';

    return DateElement.extend({
        defaults: {
            options: {
                /**
                 * Hàm này chạy cho mỗi ngày hiển thị trên lịch.
                 * @param {Date} date - Ngày đang được render
                 * @returns {Array} [Cho phép chọn (bool), Class CSS (string), Tooltip (string)]
                 */
                beforeShowDay: function (date) {
                    var day = date.getDate(); // Lấy ngày trong tháng (1-31)

                    var isAllowed = (day >= 8 && day <= 12);
                    console.log(isAllowed);
                    return [isAllowed, ""]; // Trả về mảng với giá trị cho phép chọn và class CSS rỗng
                }
            }
        }
    });
})
