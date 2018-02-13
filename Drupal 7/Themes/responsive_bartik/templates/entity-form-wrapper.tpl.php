<div class="register-form-headers_main">
    <div class="register-form-headers">
        <?php
            global $language;
            if($language->language == 'ar'){
                        echo '<div class="field-type-markup field-name-field-create-company-profile field-widget-markup form-wrapper" id="edit-profile-main-field-create-company-profile--2"><div id="profile-main-field-create-company-profile-add-more-wrapper--2"><p>إنشاء ملف تعريف الشركة</p>
            </div></div>';
                    }else{
                        echo '<div class="field-type-markup field-name-field-create-company-profile field-widget-markup form-wrapper" id="edit-profile-main-field-create-company-profile--2"><div id="profile-main-field-create-company-profile-add-more-wrapper--2"><p>Create Company <span style="color: #ff0000;">Profile</span></p>
                            <div class="home_search_circle">1</div>
            </div></div>';
            }
            ?>
    </div>
    
    <div class="register-form-headers">
        <?php
            global $language;
            if($language->language == 'ar'){
                        echo '<div class="field-type-markup field-name-field-create-company-profile field-widget-markup form-wrapper" id="edit-profile-main-field-create-company-profile--2"><div id="profile-main-field-create-company-profile-add-more-wrapper--2"><p>تصميم الهدف الخاص بك</p>
            </div></div>';
                    }else{
                        echo '<div class="field-type-markup field-name-field-create-company-profile field-widget-markup form-wrapper" id="edit-profile-main-field-create-company-profile--2"><div id="profile-main-field-create-company-profile-add-more-wrapper--2"><p>Design Your <span style="color: #ff0000;">Target</span></p>
                            <div class="home_search_circle">2</div>
            </div></div>';
        }
        ?>
    </div>
</div>
<div class="find_partner_col partner_first_half">
<?php 
print_r($form['field_please_tell_us_about_your_']['und'][0]['#title']);
    //print render($form['field_please_tell_us_about_your_']);

    $form['field_what_s_your_nationality_']['#access'] = false;
    print render($form['field_your_name']);
    print render($form['field_your_email']);
    print render($form['field_what_s_your_country_']);
    print render($form['field_partner_company_name']);
    print render($form['field_telephone_number']);
?>
</div>


<div class="find_partner_col partner_second_half">
    <?php 
        print render($form['field_what_are_you_looking_for_']);
    ?>
    <?php 
        print render($form['field_how_much_are_you_willing_t']);
    ?>
    <?php 
        print render($form['field_tell_us_what_market_you_wa']);
    ?>
</div>

<?php
    print drupal_render($form['actions']);
    print drupal_render($form['form_build_id']);
    print drupal_render_children($form);
    print drupal_render($form['form_id']);
?>

