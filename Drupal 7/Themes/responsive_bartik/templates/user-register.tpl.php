<div class="register-form-headers_main">
    <?php 
        //print render ($form['profile_main']['field_register_page_title']);
        
        global $language;
        $register_your_company_txt = $form['profile_main']['field_register_page_title']['en'][0]['#title'];
        $register_your_company_arr = explode(" ", $register_your_company_txt);
        if($language->language == 'ar'){
            echo '<div class="field-type-markup field-name-field-register-page-title field-widget-markup form-wrapper" id="edit-profile-main-field-register-page-title--2"><div id="profile-main-field-register-page-title-add-more-wrapper--2"><p>'.$register_your_company_txt.'</span></p>
</div></div>';
        }else{
            print render ($form['profile_main']['field_register_page_title']); 
        }
    ?>
    <div class="register-form-headers">
        <?php 
            
        //global $language;
        $create_company_txt = $form['profile_main']['field_create_company_profile']['en'][0]['#title']; 
        $create_company_txt_arr = explode(" ", $create_company_txt);
        if($language->language == 'ar'){
            echo '<div class="field-type-markup field-name-field-create-company-profile field-widget-markup form-wrapper" id="edit-profile-main-field-create-company-profile--2"><div id="profile-main-field-create-company-profile-add-more-wrapper--2"><p>'.$create_company_txt.'</p>
            </div></div>';
            }else{
                //echo $create_company_txt_arr[0].' '.$create_company_txt_arr[1].' <span style="color:#ff0000">'.end($create_company_txt_arr)."</span>";
                print render($form['profile_main']['field_create_company_profile']);
        }
        
        ?>
    </div>
    <div class="register-form-headers">
        <?php 
            //print render ($form['profile_main']['field_design_your_target']); 
            $design_your_target_txt = $form['profile_main']['field_design_your_target']['en'][0]['#title']; 
            $design_your_target_arr = explode(" ", $design_your_target_txt);
            if($language->language == 'ar'){
                echo '<div class="field-type-markup field-name-field-design-your-target field-widget-markup form-wrapper" id="edit-profile-main-field-design-your-target--2"><div id="profile-main-field-design-your-target-add-more-wrapper--2"><p>'.$design_your_target_txt.'</span></p>
</div></div>';
            }else{
                print render ($form['profile_main']['field_design_your_target']); 
            }
        ?>
    </div>
</div>
<div class="register-form-col-box" id="general-info">
    <div class="register-form-field-box">
        <div class="register-fields-header">
            <?php 
                //print render ($form['profile_main']['field_general_information']);
                echo $form['profile_main']['field_general_information']['en'][0]['#title']; 
            ?>
        </div>
        <?php print render ($form['profile_main']['field_company_name']); ?>
        <?php print render ($form['profile_main']['field_what_s_your_nationality']); ?>
        <?php 
            print ($form['profile_main']['field_date_of_establishment']['en'][0]['#title']);
            print render ($form['profile_main']['field_date_of_establishment']['en']); 
            
        ?>
        <?php print render ($form['profile_main']['field_list_owner_s_names']); ?>
        <?php print render ($form['profile_main']['field_what_is_your_mission_']); ?>
        <?php print render ($form['profile_main']['field_do_you_have_a_website_']); ?>
        <?php echo  $form['profile_main']['field_contacts']['en'][0]['#title']; ?>
        <?php print render ($form['profile_main']['field_tel_']); ?>
        <?php print render ($form['account']); ?>
        <?php print render ($form['profile_main']['field_upload_your_logo']); 
        
        ?>
    </div>
</div>

<div class="register-form-col-box">
    <div class="register-form-field-box">
        <div class="register-fields-header">
            <?php 
                //print render ($form['profile_main']['field_capacity']); 
                echo $form['profile_main']['field_capacity']['en'][0]['#title']; 
            ?>
        </div>
        <?php print render ($form['profile_main']['field_capital_money']); ?>
        <?php print render ($form['profile_main']['field_net_assets']); ?>
        <?php print render ($form['profile_main']['field_total_employees']); ?>
    </div>

    <div class="register-form-field-box">
        <div class="register-fields-header">
            <?php 
                //print render ($form['profile_main']['field_busniess_activites']); 
                echo $form['profile_main']['field_busniess_activites']['en'][0]['#title']; 
            ?>
        </div>
        <?php print render ($form['profile_main']['field_business_type']); ?>
        <?php 
            $form['profile_main']['field_market']['#attributes'] = array('class' => array('company_market'));
            print render ($form['profile_main']['field_market']); 
        ?>
        <?php print render ($form['profile_main']['field_services']); ?>
        
    </div>
</div>

<div class="register-form-col-box">
    <div class="register-form-field-box">
        <div class="register-fields-header">
            <?php 
                //print render ($form['profile_main']['field_product_and_service']); 
                echo $form['profile_main']['field_product_and_service']['en'][0]['#title']; 
            ?>
        </div>
        <?php print render ($form['profile_main']['field_describe_your_product']); ?>
        <?php print render ($form['profile_main']['field_add_technical_specificatio']); ?>
        <?php print render ($form['profile_main']['field_upload_your_brochure']); ?>

    </div>
    
    <div class="register-form-field-box">
        <div class="register-fields-header">
            <?php 
                //print render ($form['profile_main']['field_geography']); 
                echo $form['profile_main']['field_geography']['en'][0]['#title'];
            ?>
        </div>
        <?php 
        $options_arr = array(); //field_company_country_value
	$options_arr = $form['profile_main']['field_company_country']['und']['#options'];
        foreach($options_arr as $key => $value) {
            if($key != 'AE' && $key != 'OM' && $key != 'SA' && $key != 'BH' && $key != 'KW' && $key != 'QA'){
                unset($options_arr[$key]);
            }
	}
        //print_r($form['profile_main']['field_company_country']['und']['#options']);
        $form['profile_main']['field_company_country']['und']['#options'] = $options_arr;
        print render ($form['profile_main']['field_company_country']); ?>
        <?php print render ($form['profile_main']['field_company_city']); ?>

    </div>
</div>

<div class="register-form-col-box">
    <div class="register-form-field-box">
        <div class="register-fields-header">
            <?php 
                //print render ($form['profile_main']['field_segments']); 
                echo $form['profile_main']['field_segments']['en'][0]['#title'];
            ?>
        </div>
        <?php print render ($form['profile_main']['field_describe_your_customers']); ?>
        <?php print render ($form['profile_main']['field_any_one']); ?>
        <?php print render ($form['profile_main']['field_from']); ?>
        <?php print render ($form['profile_main']['field_to']); ?>
        <?php print render ($form['profile_main']['field_sex']); ?>
        <?php print render ($form['profile_main']['field_companies']); ?>
        <?php 
           //echo $form['ms_membership']['sku']['#options']['attend_exhibition'] = "<span class='ms_products_plan_name'>TEST</span> - <span class='ms_products_plan_price'>$20.00</span>";
            $options_arr = array();
            $sub_arr = array();
            $options_arr = $form['ms_membership']['sku']['#options'];
            foreach($options_arr as $key => $value) {
                $sub_arr = explode('-', $value);
                $options_arr[$key] = $sub_arr[0];
            }
        
            $form['ms_membership']['sku']['#options'] = $options_arr;
            print render ($form['ms_membership']); ?>
        
    </div>
</div>
<?php
    print drupal_render($form['actions']);
    print drupal_render($form['form_build_id']);
  print drupal_render($form['form_id']);
?>
<script>
jQuery('.form-radio[value=_none]').parent().hide();
</script>