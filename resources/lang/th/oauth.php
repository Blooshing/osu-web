<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

return [
    'cancel' => 'ยกเลิก',

    'authorise' => [
        'app_owner' => 'แอปพลิเคชันโดย :owner',
        'request' => 'ต้องการสิทธิ์ในการเข้าถึงบัญชีของคุณ',
        'scopes_title' => 'แอปพลิเคชันนี้จะสามารถ:',
        'title' => 'ต้องได้รับอนุญาตก่อน',
    ],

    'authorized_clients' => [
        'confirm_revoke' => 'คุณแน่ใจหรือไม่ว่าต้องการลบสิทธิ์การใช้งานนี้?',
        'scopes_title' => 'แอปพลิเคชันนี้สามารถ:',
        'owned_by' => ':user เป็นเจ้าของ',
        'none' => 'ไม่มีไคลเอนต์',

        'revoked' => [
            'false' => 'ยกเลิกการเข้าถึง',
            'true' => 'ยกเลิกการเข้าถึงแล้ว',
        ],
    ],

    'client' => [
        'id' => 'ID ของไคลเอนต์',
        'name' => 'ชื่อแอปพลิเคชัน',
        'redirect' => 'URL การเรียกกลับของแอปพลิเคชัน',
        'reset' => 'รีเซ็ตรหัสลับไคลเอ็นต์',
        'reset_failed' => 'ไม่สามารถรีเซ็ตรหัสลับไคลเอ็นต์',
        'secret' => 'รหัสลับไคลเอ็นต์',

        'secret_visible' => [
            'false' => 'แสดงรหัสลับไคลเอ็นต์',
            'true' => 'ซ่อนรหัสลับไคลเอ็นต์',
        ],
    ],

    'new_client' => [
        'header' => 'ลงทะเบียนแอปพลิเคชัน OAuth ใหม่',
        'register' => 'ลงทะเบียนแอปพลิเคชั่น',
        'terms_of_use' => [
            '_' => 'โดยการใช้ API จะถือว่าคุณยอมรับ :link',
            'link' => 'ข้อกำหนดการใช้งาน',
        ],
    ],

    'own_clients' => [
        'confirm_delete' => 'คุณแน่ใจหรือว่าต้องการลบไคลเอนต์นี้?',
        'confirm_reset' => 'คุณแน่ใจหรือว่าต้องการรีเซ็ต Client Secret? การดำเนินการนี้จะยกเลิกโทเค็นที่มีอยู่ทั้งหมด',
        'new' => 'แอปพลิเคชัน OAuth ใหม่',
        'none' => 'ไม่มีไคลเอนต์',

        'revoked' => [
            'false' => 'ลบ',
            'true' => 'ถูกลบแล้ว',
        ],
    ],
];
