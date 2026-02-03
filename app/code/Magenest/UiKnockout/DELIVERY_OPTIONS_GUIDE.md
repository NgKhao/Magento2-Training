# 📦 Bài tập: Delivery Time Options

## 🎯 Mục tiêu
Tạo chức năng cho phép khách hàng chọn thời gian giao hàng trên trang Product Detail:
- **Option 1:** Giao hàng trong ngày
- **Option 2:** Chọn ngày giao hàng tùy chỉnh (datepicker)

Giá trị được chọn sẽ được lưu vào Order khi khách hàng Add to Cart.

---

## 📁 Cấu trúc File

```
app/code/Magenest/UiKnockout/
├── view/frontend/
│   ├── layout/
│   │   └── catalog_product_view.xml          ← Khai báo block trên trang Product Detail
│   ├── templates/
│   │   └── delivery_time.phtml                ← Template chính (wrapper)
│   └── web/
│       ├── js/
│       │   └── delivery-options.js            ← JS Component (logic)
│       └── template/
│           └── delivery-template.html         ← HTML Template (giao diện)
```

---

## 🔄 Luồng hoạt động

### **1. Layout XML** (`catalog_product_view.xml`)
```xml
<block template="Magenest_UiKnockout::delivery_time.phtml">
    <arguments>
        <argument name="jsLayout">
            <item name="delivery-options">
                <item name="component">Magenest_UiKnockout/js/delivery-options</item>
                <item name="template">Magenest_UiKnockout/delivery-template</item>
            </item>
        </argument>
    </arguments>
</block>
```

**Nhiệm vụ:**
- Thêm block vào trang Product Detail
- Truyền cấu hình `jsLayout` cho Component

---

### **2. PHTML Template** (`delivery_time.phtml`)
```html
<div id="delivery-options-component" data-bind="scope: 'delivery-options'">
    <!-- ko template: getTemplate() --><!-- /ko -->
</div>

<input type="hidden" name="options[4]" value="" />

<script type="text/x-magento-init">
{
    "#delivery-options-component": {
        "Magento_Ui/js/core/app": <?= $block->getJsLayout() ?>
    }
}
</script>
```

**Nhiệm vụ:**
- Tạo wrapper div với scope KnockoutJS
- Tạo input ẩn `options[4]` để lưu giá trị
- Khởi tạo Magento UI Component

---

### **3. JS Component** (`delivery-options.js`)
```javascript
return Component.extend({
    defaults: {
        targetInputName: 'options[4]'
    },
    
    initialize: function () {
        this._super();
        
        // Khởi tạo Observable
        this.deliveryType = ko.observable('today');
        this.selectedDate = ko.observable('');
        this.finalValue = ko.observable('Giao hàng trong ngày');
        
        // Subscribe để lắng nghe thay đổi
        this.finalValue.subscribe(function (newValue) {
            this.updateNativeInput(newValue);
        }, this);
    },
    
    selectType: function (type) {
        // Logic khi chọn radio button
    },
    
    updateNativeInput: function (value) {
        // Cập nhật giá trị vào input ẩn
    }
});
```

**Nhiệm vụ:**
- Quản lý state với KnockoutJS Observable
- Xử lý logic khi user chọn option
- Cập nhật giá trị vào input ẩn

---

### **4. HTML Template** (`delivery-template.html`)
```html
<div class="delivery-group">
    <!-- Radio 1: Giao trong ngày -->
    <input type="radio" 
           data-bind="checked: deliveryType, click: selectType.bind($data, 'today')">
    
    <!-- Radio 2: Chọn ngày -->
    <input type="radio" 
           data-bind="checked: deliveryType, click: selectType.bind($data, 'custom')">
    
    <!-- Datepicker (chỉ hiện khi chọn custom) -->
    <div data-bind="visible: deliveryType() === 'custom'">
        <input type="text" 
               data-bind="datepicker: { storage: selectedDate, options: getDatePickerOptions() }" />
    </div>
    
    <!-- Debug info -->
    <div>Giá trị: <span data-bind="text: finalValue"></span></div>
</div>
```

**Nhiệm vụ:**
- Render giao diện với KnockoutJS bindings
- Hiển thị/ẩn datepicker tự động
- Hiển thị giá trị debug

---

## 🔑 Các khái niệm quan trọng

### **1. KnockoutJS Observable**
```javascript
// SAI - Không khai báo trong defaults
defaults: {
    myVar: ko.observable('value') // ❌
}

// ĐÚNG - Khai báo trong initialize
initialize: function () {
    this.myVar = ko.observable('value'); // ✅
}
```

**Lý do:** Observable cần được khởi tạo tại runtime, không phải lúc define class.

---

### **2. KnockoutJS Subscribe**
```javascript
this.myVar.subscribe(function (newValue) {
    console.log('Giá trị mới:', newValue);
});
```

**Công dụng:** Lắng nghe thay đổi của Observable và thực thi callback.

---

### **3. Data Binding trong Template**

| Binding | Mô tả | Ví dụ |
|---------|-------|-------|
| `checked` | Liên kết radio/checkbox với Observable | `checked: deliveryType` |
| `click` | Gọi function khi click | `click: selectType.bind($data, 'today')` |
| `visible` | Hiện/ẩn element dựa vào điều kiện | `visible: deliveryType() === 'custom'` |
| `text` | Hiển thị text từ Observable | `text: finalValue` |
| `value` | Liên kết giá trị input | `value: myInput` |
| `datepicker` | Khởi tạo datepicker (custom của Magento) | `datepicker: { storage: selectedDate }` |

---

### **4. Magento UI Component Flow**

```
Layout XML (jsLayout config)
    ↓
PHTML (x-magento-init)
    ↓
Magento_Ui/js/core/app (khởi tạo component)
    ↓
JS Component (initialize)
    ↓
HTML Template (render với KnockoutJS)
    ↓
User Interaction (radio, datepicker)
    ↓
Observable thay đổi
    ↓
Subscribe callback chạy
    ↓
Update input ẩn
    ↓
Add to Cart (gửi lên server)
```

---

## 🧪 Cách Test

### **1. Kiểm tra Component khởi tạo**
- Mở Console → Phải thấy log: `✅ Delivery Options Component đã khởi tạo!`

### **2. Kiểm tra Radio Button**
- Click vào "Giao trong ngày" → Debug info phải hiện: `Giao hàng trong ngày`
- Click vào "Chọn ngày" → Datepicker phải xuất hiện

### **3. Kiểm tra Datepicker**
- Chọn ngày từ datepicker
- Debug info phải hiện: `Giao ngày: 28/01/2026`

### **4. Kiểm tra Input ẩn**
- Inspect element → Tìm `<input name="options[4]">`
- Value phải khớp với Debug info

### **5. Kiểm tra Add to Cart**
- Chọn thời gian → Click Add to Cart
- Vào Shopping Cart → Xem Order Item
- Phải thấy thông tin thời gian giao hàng

---

## 🐛 Troubleshooting

### **Lỗi: Component không khởi tạo**
**Nguyên nhân:** 
- File JS không tồn tại hoặc path sai
- Cache chưa xóa

**Giải pháp:**
```bash
rm -rf pub/static/frontend/* var/view_preprocessed/* generated/code/*
php bin/magento cache:flush
```

---

### **Lỗi: Template không render**
**Nguyên nhân:** 
- File template không tồn tại
- Path template sai trong jsLayout

**Kiểm tra:**
- Path phải là: `view/frontend/web/template/delivery-template.html`
- Trong jsLayout: `Magenest_UiKnockout/delivery-template` (không có .html)

---

### **Lỗi: Datepicker không hiện**
**Nguyên nhân:** 
- Chưa require `mage/calendar`
- Binding sai cú pháp

**Giải pháp:**
- Kiểm tra define: `'mage/calendar'`
- Kiểm tra binding: `datepicker: { storage: selectedDate, options: getDatePickerOptions() }`

---

### **Lỗi: Input ẩn không được update**
**Nguyên nhân:** 
- `targetInputName` không khớp với name của input
- jQuery không tìm thấy element

**Kiểm tra Console:**
```
❌ Không tìm thấy input: options[4]
```

**Giải pháp:**
- Đảm bảo input được tạo trong PHTML: `<input name="options[4]" />`
- Kiểm tra `targetInputName` trong jsLayout

---

## 💡 Mở rộng

### **1. Thêm validation**
```javascript
selectType: function (type) {
    if (type === 'custom' && !this.selectedDate()) {
        alert('Vui lòng chọn ngày giao hàng!');
        return false;
    }
    // ...
}
```

### **2. Thêm giá phí theo ngày**
```javascript
this.deliveryFee = ko.computed(function () {
    if (this.deliveryType() === 'today') {
        return 50000; // Phí ship nhanh
    }
    return 20000; // Phí ship thường
}, this);
```

### **3. Lưu vào localStorage**
```javascript
this.finalValue.subscribe(function (value) {
    localStorage.setItem('delivery_time', value);
});
```

---

## 📚 Tài liệu tham khảo

- [Magento UI Components](https://developer.adobe.com/commerce/frontend-core/ui-components/)
- [KnockoutJS Documentation](https://knockoutjs.com/documentation/introduction.html)
- [Magento Calendar Widget](https://developer.adobe.com/commerce/frontend-core/javascript/jquery-widgets/calendar/)

---

## ✅ Checklist hoàn thành

- [x] Layout XML đúng cấu trúc
- [x] PHTML template có input ẩn
- [x] JS Component khai báo Observable đúng cách
- [x] HTML Template có KnockoutJS bindings
- [x] Datepicker hoạt động
- [x] Giá trị được cập nhật vào input ẩn
- [x] Console log rõ ràng để debug
- [x] CSS đẹp và responsive

---

**Chúc em học tốt! 🎓**

