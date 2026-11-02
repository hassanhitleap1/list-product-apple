<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    // Trade-in calculator settings.
    // Each damage lowers the Grade A price by this share (0.4 = 40%).
    'tradeInDamages' => [
        'lcd_broken' => ['label' => 'Screen (LCD) broken', 'label_ar' => 'الشاشة مكسورة', 'deduct' => 0.4],
        'pack_cover_broken' => ['label' => 'Back cover broken', 'label_ar' => 'الغطاء الخلفي مكسور', 'deduct' => 0.1],
        'body_scratched' => ['label' => 'Body scratched', 'label_ar' => 'خدوش في الهيكل', 'deduct' => 0.1],
    ],
    // The low end of the offer is the high end multiplied by this (0.75 = 25% lower).
    'tradeInLowFactor' => 0.75,
];
