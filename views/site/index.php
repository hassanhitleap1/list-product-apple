<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\IphoneModels;
use app\models\BattaryModels;


$this->title = 'action';
$models = ArrayHelper::map(IphoneModels::find()->all(), 'id', 'model');
$battary = ArrayHelper::map(BattaryModels::find()->all(), 'id', 'value');

$modelsJson = json_encode(ArrayHelper::map(IphoneModels::find()->all(), 'id', function ($model) {
    return [
        'id' => $model->id,
        'price' => $model->price,
    ];
}));


$battaryJson = json_encode(ArrayHelper::map(BattaryModels::find()->all(), 'id', function ($model) {
    return [
        'id' => $model->id,
        'price' => $model->price,
    ];
}));



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



<div class="dynamic-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
     
        <div class="col-12">
            <?= $form->field($model, 'model')->dropDownList($models, [
                'prompt' => 'please select',
                'id' => 'iphoneModel'
            ])->label('Iphone Model') ?>

    </div>


    <div class="row mt-4">
     
        <div class="col-12">
            <!-- Damage Radio Buttons -->
            <div id="damageGroup" class="hidden">
                <p>Damage?</p>
                <?= Html::radioList('damage', null, ['yes' => 'Yes', 'no' => 'No'], ['id' => 'damage']) ?>
            </div>

        </div>
     
    </div>




    <div class="row mt-4">
    
        <div class="col-12">
            <!-- Scratched Radio Buttons -->
            <div id="scratchedGroup" class="hidden">
                <p>scratched?</p>
                <?= Html::radioList('scratched', null, ['yes' => 'Yes', 'no' => 'No'], ['id' => 'scratched']) ?>
            </div>
        </div>
   
    </div>
    <div class="row mt-4">
     
        <div class="col-12">
            <!-- Additional Damage Checklist -->
            <div id="additionalDamageGroup" class="hidden">


                <p>Select other damages:</p>
                <?= Html::checkboxList('additionalDamage', null, [
                    'lcd_broken' => 'LCD Broken',
                    'pack_cover_broken' => 'Pack Cover Broken',
                    'body_scratched' => 'Body Scratched'
                ], [
                    'id' => 'additionalDamage',
                    'item' => function ($index, $label, $name, $checked, $value) {
                        return Html::checkbox($name, $checked, [
                            'value' => $value,
                            'style' => 'width: 20px; height: 20px;', 
                            'data-damage-multiplier-percent' => [
                                'lcd_broken' => 0.4,
                                'pack_cover_broken' => 0.1,
                                'body_scratched' => 0.1
                            ][$value]
                        ]) . Html::tag('span', $label);;
                    },
                ]) ?>

            </div>
        </div>
     
    </div>
    <div class="row mt-4">
    
        <div class="col-12">
            <!-- Battery Dropdown List -->
            <div id="batteryGroup" class="hidden">
                <?= $form->field($model, 'batteryModel')->dropDownList($battary, [
                    'prompt' => 'Select Battery Model',
                    'id' => 'batteryModel'
                ]) ?>
            </div>
        </div>

    </div>
    <!-- Price Display -->
    <div id="priceDisplay" class="hidden alert alert-info mt-10">
        <div class="row">
            <div class="col-10">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-info btn-sm float-right" @click="calculatePrice">=</button>
            </div>
        </div>

        <p>Calculated from:
        <span id="calculatedPrice">0 JOD</span>
        to     
        <span id="calculatedPriceFrom">0 JOD</span>
        </p>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<?php
$script = <<< JS
    var models = {$modelsJson};
    var battaris = {$battaryJson};
    $('#iphoneModel').change(function() {
        $('#damageGroup').removeClass('hidden');
        $('#priceDisplay').addClass('hidden');
    });

    $('input[name="damage"]').change(function() {
        // set all additionalDamageGroup value unchecked
        $('input[name="additionalDamage[]"]').prop('checked', false);
        if ($(this).val() === 'yes') {
            $('#scratchedGroup').addClass('hidden');
            $('#additionalDamageGroup').removeClass('hidden');
        } else {
            $('#scratchedGroup').removeClass('hidden');
            $('#additionalDamageGroup').addClass('hidden');
        }


        if($('#batteryGroup').value) {
            calculatePrice();
        }

    });

    $('input[name="scratched"]').change(function() {
        if ($(this).val() === 'yes') {
            $('#batteryGroup').addClass('hidden');
            $('#additionalDamageGroup').removeClass('hidden');
        } else {
            $('#batteryGroup').removeClass('hidden');
        }
    });

    $('input[name="additionalDamage[]"]').change(function() {
        $('#batteryGroup').removeClass('hidden');

        console.log("batteryModel",$('#batteryModel').val())

        if($('#batteryModel').val()) {
            calculatePrice();
        }
   
   
        
    });

    $('#batteryGroup').change(function() {
        calculatePrice();
    });

    
    function calculatePrice() {
        var selectedValue = $('#iphoneModel').val(); // assuming you have a select element with id "iphoneModel"
        var  model = models[selectedValue];
        let basePrice = model.price; 

        var totalDamageMultiplierPercent = 0;
       

        $('#additionalDamage input[type="checkbox"]:checked').each(function() {
            try {
                var damageMultiplierPercent = parseFloat( $(this).attr('data-damage-multiplier-percent'));
            if (isNaN(damageMultiplierPercent)) {
                damageMultiplierPercent = 0;
            }
            totalDamageMultiplierPercent +=damageMultiplierPercent   
            }catch (error) {
                console.log(error);
            }
       

            // do something with the value and damageMultiplierPercent
        });

        var selectedValuebatteryModel = $('#batteryModel').val(); // assuming you have a select element with id "iphoneModel"
        var  battary = battaris[selectedValuebatteryModel];
        let batteryParcent = battary.price;
        let perasantage= batteryParcent + totalDamageMultiplierPercent;
        perasantage= perasantage.toFixed(2)


    

        perasantage = parseFloat(perasantage);

        console.log("batteryParcent",batteryParcent);

        console.log("totalDamageMultiplierPercent",totalDamageMultiplierPercent)

        console.log("basePrice",basePrice)

        console.log("perasantage",perasantage)

       



        let calculatedPrice = basePrice - (basePrice * perasantage);

      
    
        $('#calculatedPrice').text( calculatedPrice  + ' JOD' );

        console.log("calculatedPrice",calculatedPrice)

        // calculatedPrice = parseFloat(calculatedPrice.toFixed(2));


        let calculatedPriceForm =  calculatedPrice  - (calculatedPrice * 0.25);
        
        
        $('#calculatedPriceFrom').text(calculatedPriceForm +' JOD' );

        

        $('#priceDisplay').removeClass('hidden');
    }
JS;
$this->registerJs($script);
?>


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