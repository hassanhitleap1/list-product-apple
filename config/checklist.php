<?php

/**
 * Trade-in checklist: the questions the calculator asks, in order.
 * Based on the Action Mobile "Mobile Checklist" sheet.
 *
 * Each step:
 *   id        unique key
 *   title     question (English), title_ar (Arabic)
 *   short     optional short name for the summary and messages
 *   help      optional hint under the question
 *   when      optional: only ask if another step has a given answer,
 *             e.g. ['step' => 'icloud', 'is' => 'on']
 *   visual    optional: 'scratch' shows the iPhone / Samsung drawing
 *   options   answers. Each answer can have:
 *               deduct  => share taken off the price (0.35 = 35%)
 *               reject  => true stops the process and shows the branch message
 *               confirm => text the customer must confirm before going on
 *
 * If all deductions together reach 100% or more, the device is rejected too.
 */

$faulty = function ($deduct) {
    return [
        ['value' => 'ok', 'label' => 'No fault', 'label_ar' => 'يعمل بشكل سليم'],
        ['value' => 'faulty', 'label' => 'Faulty', 'label_ar' => 'فيه عطل', 'deduct' => $deduct],
    ];
};

return [
    'rejectMessage' => 'Please visit one of our branches so we can help you evaluate your device.',
    'rejectMessage_ar' => 'الرجاء مراجعة أحد فروعنا لمساعدتكم في تقييم الجهاز.',
    // Phone number shown with the reject message (tap to call on mobile).
    'branchPhone' => '0791009595',
    'branchPhoneIntl' => '+962791009595',

    // The low end of the offer is the high end multiplied by this (0.75 = 25% lower).
    'lowFactor' => 0.75,

    'steps' => [
        [
            'id' => 'icloud',
            'short' => 'iCloud / Find My',
            'title' => 'Is iCloud / Find My Phone on?',
            'title_ar' => 'هل خاصية iCloud / Find My Phone مفعّلة؟',
            'help' => 'On Samsung this is Samsung account / Find My Mobile.',
            'help_ar' => 'على سامسونج: حساب سامسونج / Find My Mobile.',
            'options' => [
                ['value' => 'off', 'label' => 'Off', 'label_ar' => 'غير مفعّلة'],
                ['value' => 'on', 'label' => 'On', 'label_ar' => 'مفعّلة'],
            ],
        ],
        [
            'id' => 'icloud_status',
            'short' => 'Device status',
            'when' => ['step' => 'icloud', 'is' => 'on'],
            'title' => 'Is the device clean or marked as lost?',
            'title_ar' => 'هل الجهاز سليم (Clean) أم مسجّل كمفقود (Lost)؟',
            'options' => [
                ['value' => 'clean', 'label' => 'Clean', 'label_ar' => 'سليم'],
                ['value' => 'lost', 'label' => 'Lost', 'label_ar' => 'مفقود', 'reject' => true],
            ],
        ],
        [
            'id' => 'icloud_password',
            'short' => 'Account password',
            'when' => ['step' => 'icloud_status', 'is' => 'clean'],
            'title' => 'Do you know the account password?',
            'title_ar' => 'هل تعرف كلمة سر الحساب؟',
            'options' => [
                [
                    'value' => 'yes', 'label' => 'Yes', 'label_ar' => 'نعم',
                    'confirm' => 'I will sign out of the account in store before handing over the device.',
                    'confirm_ar' => 'سأقوم بتسجيل الخروج من الحساب في المحل قبل تسليم الجهاز.',
                ],
                ['value' => 'no', 'label' => 'No', 'label_ar' => 'لا', 'reject' => true],
            ],
        ],
        [
            'id' => 'sim',
            'short' => 'SIM lock',
            'title' => 'SIM lock status',
            'title_ar' => 'حالة قفل الشريحة',
            'options' => [
                ['value' => 'unlocked', 'label' => 'Unlocked', 'label_ar' => 'مفتوح'],
                ['value' => 'locked', 'label' => 'Locked', 'label_ar' => 'مقفول', 'reject' => true],
            ],
        ],
        [
            'id' => 'warranty',
            'short' => 'Warranty',
            'title' => 'Warranty status',
            'title_ar' => 'حالة الكفالة',
            'options' => [
                ['value' => 'active', 'label' => 'Active', 'label_ar' => 'فعّالة'],
                ['value' => 'expired', 'label' => 'Expired', 'label_ar' => 'منتهية', 'deduct' => 0.05],
            ],
        ],
        [
            'id' => 'battery',
            'short' => 'Battery',
            'title' => 'Battery health',
            'title_ar' => 'صحة البطارية',
            'help' => 'iPhone: Settings › Battery › Battery Health. Samsung: Settings › Battery › Battery information.',
            'help_ar' => 'آيفون: الإعدادات › البطارية › صحة البطارية. سامسونج: الإعدادات › البطارية › معلومات البطارية.',
            'options' => [
                ['value' => '95-100', 'label' => '95% – 100%'],
                ['value' => '90-95', 'label' => '90% – 95%'],
                ['value' => '80-90', 'label' => '80% – 90%', 'deduct' => 0.05],
                ['value' => 'lt80', 'label' => 'Less than 80%', 'label_ar' => 'أقل من 80%', 'deduct' => 0.15],
            ],
        ],
        [
            'id' => 'damage',
            'short' => 'Damage',
            'title' => 'Is the device damaged?',
            'title_ar' => 'هل الجهاز متضرر؟',
            'help' => 'For example bent, water damage, or broken parts.',
            'help_ar' => 'مثل الانحناء، أو دخول الماء، أو قطع مكسورة.',
            'options' => [
                ['value' => 'no', 'label' => 'No', 'label_ar' => 'لا'],
                ['value' => 'yes', 'label' => 'Yes', 'label_ar' => 'نعم', 'reject' => true],
            ],
        ],
        ['id' => 'lcd', 'title' => 'Screen (LCD)', 'title_ar' => 'الشاشة', 'options' => $faulty(0.35)],
        ['id' => 'camera', 'title' => 'Camera', 'title_ar' => 'الكاميرا', 'options' => $faulty(0.20)],
        ['id' => 'signal', 'title' => 'Signal', 'title_ar' => 'الإشارة (الشبكة)', 'options' => $faulty(0.15)],
        ['id' => 'wifi', 'title' => 'Wi‑Fi', 'title_ar' => 'الواي فاي', 'options' => $faulty(0.15)],
        ['id' => 'earphone', 'title' => 'Earpiece speaker', 'title_ar' => 'سماعة المكالمات', 'options' => $faulty(0.15)],
        ['id' => 'mic', 'title' => 'Microphone', 'title_ar' => 'المايك', 'options' => $faulty(0.10)],
        ['id' => 'charging', 'title' => 'Charging port', 'title_ar' => 'منفذ الشحن', 'options' => $faulty(0.15)],
        [
            'id' => 'body',
            'short' => 'Body',
            'title' => 'Body condition',
            'title_ar' => 'حالة الهيكل',
            'options' => [
                ['value' => 'ok', 'label' => 'No damage', 'label_ar' => 'بدون أضرار'],
                ['value' => 'damaged', 'label' => 'Damaged', 'label_ar' => 'فيه أضرار', 'deduct' => 0.30],
            ],
        ],
        [
            'id' => 'software',
            'short' => 'Software',
            'title' => 'Is the software still supported?',
            'title_ar' => 'هل نظام التشغيل مدعوم؟',
            'options' => [
                ['value' => 'supported', 'label' => 'Supported', 'label_ar' => 'مدعوم'],
                ['value' => 'unsupported', 'label' => 'Not supported', 'label_ar' => 'غير مدعوم', 'deduct' => 0.10],
            ],
        ],
        [
            'id' => 'scratch',
            'short' => 'Scratches',
            'title' => 'Does the device have scratches?',
            'title_ar' => 'هل يوجد خدوش على الجهاز؟',
            'options' => [
                ['value' => 'no', 'label' => 'No', 'label_ar' => 'لا'],
                ['value' => 'yes', 'label' => 'Yes', 'label_ar' => 'نعم'],
            ],
        ],
        [
            'id' => 'scratch_pct',
            'short' => 'Scratch amount',
            'when' => ['step' => 'scratch', 'is' => 'yes'],
            'visual' => 'scratch',
            'title' => 'How much of the device is scratched?',
            'title_ar' => 'ما نسبة الخدوش على الجهاز؟',
            'help' => 'Look at the front and back together.',
            'help_ar' => 'انظر إلى الواجهة والظهر معاً.',
            'options' => [
                ['value' => '25', 'label' => '25%', 'deduct' => 0.10],
                ['value' => '50', 'label' => '50%', 'deduct' => 0.20],
                ['value' => '75', 'label' => '75%', 'deduct' => 0.30],
                ['value' => '90', 'label' => '90%', 'deduct' => 0.40],
            ],
        ],
    ],
];
