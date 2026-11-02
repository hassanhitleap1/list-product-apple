<?php 

use yii\web\JqueryAsset;
JqueryAsset::register($this);

$arrIphone=['iPhone 11','iPhone 11 Pro','iPhone 12','iPhone 12 pro'];
$arrCapicity=['64GB','128GB','256GB','512GB'];
foreach ($arrIphone as $key => $phone) {
    foreach ($arrCapicity as $key => $capicity) {
       echo "insert into iphone_models (model,price) values('$phone $capicity',500); <br>" ;
    }
}



$arrIphone=['iPhone 11 Pro Max','iPhone 12 Pro Max','iPhone 13','iPhone 13 Pro','iPhone 14','iPhone 15'];
$arrCapicity=['128GB','256GB','512GB'];

foreach ($arrIphone as $key => $phone) {
    foreach ($arrCapicity as $key => $capicity) {
        echo "insert into iphone_models (model,price) values('$phone $capicity',500); <br>" ;
    }
}




$arrIphone=['iPhone 13 Pro Max','iPhone 14 plus','iPhone 14 Pro','iPhone pro max','iPhone 15 plus','iPhone 15 Pro','iPhone 15 Pro Max'];
$arrCapicity=['128GB','256GB','512GB'.'1TB'];
foreach ($arrIphone as $key => $phone) {
    foreach ($arrCapicity as $key => $capicity) {
        echo "insert into iphone_models (model,price) values('$phone $capicity',500); <br>" ;
    }
}



?>




<div id="popup">
    <p>
        Users are responsible for verifying the accuracy of the information provided on our platform. We strongly recommend checking all details through direct communication with our team via phone. Action Mobile is not responsible for any inaccurate information or discrepancies in the information provided. Users should not rely solely on the information presented on our platform without verification. Any use or reliance on the information provided is at the user’s own risk. To ensure accuracy, please verify all information by contacting us directly via phone or manually verifying the details.
    </p>
    التحقق: يتحمل المستخدمون مسؤولية التحقق من دقة المعلومات المقدمة على منصتنا. ونوصي بشدة بالتحقق من كافة التفاصيل من خلال التواصل المباشر مع فريقنا عبر الهاتف.
حدود المسؤولية: لا تتحمل شركة Action Mobile مسؤولية أي معلومات غير دقيقة أو تناقضات في المعلومات المقدمة. يجب ألا يعتمد المستخدمون فقط على المعلومات المقدمة على منصتنا دون التحقق.
الاعتماد على المعلومات: أي استخدام أو الاعتماد على المعلومات المقدمة يكون على مسؤولية المستخدم الخاصة. لضمان الدقة، يرجى التحقق من جميع المعلومات عن طريق الاتصال بنا مباشرة عبر الهاتف أو التحقق بشكل يدوي
    <p>


    </p>
    <button id="accept-button">Accept</button>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php
$script = <<< JS
    $(document).ready(function () {
        // Show the popup when the page loads
        $('#popup, #popup-overlay').fadeIn();

        // Close the popup when the Accept button is clicked
        $('#accept-button').click(function () {
            $('#popup, #popup-overlay').fadeOut();
        });
    });
JS;
$this->registerJs($script);
?>