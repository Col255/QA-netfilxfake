<?php
/**
 * Helper tính toán thời hạn VIP dùng chung cho phân hệ VIP Packages & Payment
 * Tuân thủ tiêu chuẩn PSR-12, loại bỏ trùng lặp mã nguồn (DRY)
 */

/**
 * Tính toán thời điểm hết hạn VIP mới khi đăng ký hoặc gia hạn
 * 
 * @param string|null $current_expiry_str Ngày hết hạn hiện tại của user trong DB
 * @param string $duration Thời gian cộng thêm (ví dụ: '1 month', '3 months', '6 months')
 * @return DateTime Đối tượng DateTime chứa hạn VIP mới
 */
function calculate_vip_expiry(?string $current_expiry_str, string $duration = '1 month'): DateTime {
    $new_expiry = new DateTime();
    if ($current_expiry_str !== null && strtotime($current_expiry_str) > time()) {
        try {
            $new_expiry = new DateTime($current_expiry_str);
        } catch (Exception) {
            $new_expiry = new DateTime();
        }
    }

    $modify_str = str_starts_with($duration, '+') ? $duration : '+' . $duration;
    $new_expiry->modify($modify_str);

    return $new_expiry;
}
