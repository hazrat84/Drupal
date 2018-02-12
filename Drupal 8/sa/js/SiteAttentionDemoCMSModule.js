/**
 * SiteAttention Demo CMS Module
 *
 * This is an example usage of the SiteAttention Manager Library. This code
 * should inspire the developers when write the real implementation of the CMS
 * Module.
 *
 * Author      Marius Jigoreanu    2017-02-15 09:30:21
 */

let SiteAttentionDemoCMSModule = function ( data )
{

    /**
     * Makes an AJAX call to the CMS server where the license key is saved.
     *
     * @param      {String}  key     The license key to be saved.
     */

    let save_license = function ( result , key )
    {
        let url = 'demo.client-cms.com/sa-key-save/',
            data =
            {
                key : key
            }

        ajax ( url , data );
    }

    // on submission of content call publish url
    jQuery(".js-form-submit").click(function(e) {
       e.preventDefault();
       let class_arr = this.parentNode.className.split(" ");
       let sa_key   = drupalSettings.SA.KEY;
       let sa_iid   = drupalSettings.SA.IID;
       let sa_iname = drupalSettings.SA.INAME;
       let api_url = 'https://dev.siteattention.com/'+sa_key;
       let page_id  = drupalSettings.SA.NodeID; // pid
       let page_url = drupalSettings.SA.pageUrl; // page url
       let publish_status = (class_arr[0] == "publish")? true:false;
       alert(publish_status);
       
       if(sa_key != null && sa_key != ''){ // if key is null means user is not yet registered so can't send ajax request to the server without key
           if(page_id != null){alert('page is not null');
                /*jQuery.ajax({
                    type : "POST",
                    url : api_url,
                    data : {
                        func: 'publish',
                        published: publish_status,
                        url:  page_url,
                        pid:  page_id,
                        iid:  sa_iid
                    },
                    beforeSend: function(xhr){xhr.setRequestHeader('X-SiteAttention', sa_key);},
                    success : function(responseData) {
                        console.log('Response ' + JSON.stringify(responseData));
                        jQuery('form').unbind('submit').submit();
                    }
                });*/
                jQuery('form').unbind('submit').submit();
            }else{
                jQuery('form').unbind('submit').submit(); // when page id is empty in case of add so don't send publish call
            }   
       }else{
            jQuery('form').unbind('submit').submit();
       }
    });


    /**
     * Sample function for generating an article ID. It is recomended that the real
     * implementation uses some sort of unique article/ document ID specific to
     * the CMS. Some CMS might have the same assign the same ID for and article
     * that has multiple languages. Use the language toghether with the ID
     * generate a UID.
     *
     * @return     {Number}  The UID of the article/ document of 32 length.
     */

    let get_pid = function ()
    {   
        return drupalSettings.SA.NodeID;
    }

    /**
     * Sample function for generating an CMS instance ID.This ID needs to be
     * unique per CMS instance. This means it needs to be independant of the
     * domain. This ID needs to be static or in other words it cannot change
     * during the lifetime of the CMS.
     *
     * @return     {String}  The IID of the CMS instance.
     */

   let get_iid = function ()
    {
        return 'qHqgUJHnIs';
    }


    /**
     * Returns the type of the page. The implementation should use CMS specific
     * framework API calls to figure out the type of hte content. For instance
     * it can be an article, page, product, frontpage, etc.
     *
     * @return     {String}  The type of the page.
     */

    let get_type = function ()
    {
        return drupalSettings.SA.ContentType;
    }




    /**
     * Returns the language of the article. This language string is of two
     * characters and should always be returned.
     *
     * @return     {String}  The language of the page.
     */
    let get_lang = function ()
    {
        if(drupalSettings.path.currentLanguage != ''){
            return drupalSettings.path.currentLanguage;
        }
        return 'en';
    }


    /**
     * Sample function that returns the user that is currently logged on the page.
     * The real implementation should find a way that is specific to the CMS to
     * fetch the human readable user name.
     *
     * @return     {String}  The user.
     */

    let get_user = function ()
    {
        return drupalSettings.SA.username;
    }

    /**
     * This method creates and return the container where SiteAttention should
     * live in. SiteAttention will offer instruction of where this element
     * should be place, specific per CMS.
     *
     * @return     {Node}  The CSS selector of the element.
     */

    let get_container = function ()
    {
        let form_margin_right = 0;
        switch(drupalSettings.ajaxPageState.theme){

            case'seven':
                form_margin_right = '350px';
            break;
            case'bartik':
                let header_div = document.getElementById("header");

                Object.assign( header_div.style ,{
                    paddingRight: '350px'
                });

                let main_div = document.getElementById("main");

                Object.assign( main_div.style ,{
                    marginRight: '360px'
                });

                form_margin_right = '0';

                let section_dev = document.getElementsByClassName("section");

                Object.assign( section_dev[0].style ,{
                    marginRight: '0'
                });
            break;
        }

        let node_form = document.getElementsByClassName("node-form");

        Object.assign( node_form[0].style ,{
            marginRight: form_margin_right
        });


        let div = document.createElement ( 'div' );

        div.id = 'SiteAttention_container';
        
        Object.assign( div.style ,
        {
            position: 'fixed',
            top: '0',
            right: '0',
            height: '100%',
            zIndex: '998000'
        });

        document.body.appendChild ( div );
        
        return div;
    }

    

    /**
     * This function return if SiteAttention should be minimised on startup or
     * not. This depends on the specific CMS. For instance, in EPIServer, if
     * the Blocks sidebar is opened then SiteAttention should start minimised.
     *
     * @return     {Boolean}  Minimised state
     */

    let get_minimised = function ()
    {
        return false;
    }


    /**
     * Sample function that returns the public URL of the page, in case it has
     * one.
     *
     * @return     {String}  The public URL.
     */

    let get_url = function ()
    {
        return drupalSettings.SA.pageUrl;
    }




    /**
     * Sample function that returns wether the page is published or not.
     *
     * @return     {String}  Returns if the page is published.
     */

    let get_published = function ()
    {
        return true;
    }



    /**
     * This array represents the data that it is needed to create the Field
     * classes. You have to create this yourself.
     *
     * @type       {Array}
     */

    let form_id   = jQuery('form.node-form').attr('id');
    let elements  = document.forms[form_id].elements;
    let fields    = [];
    let fieldType = '';
    let content_field_id = '';
    let seo_type_arr = new Array();
    seo_type_arr = {'text': 'title', 'textarea': 'content', 'FieldTinyMCE3': 'content', 'FieldTinyMCE4': 'content', 'file': 'images', 
                    'video': 'videos', 'links': 'links'};

    /**
    * Check that whether tinymce is used or not
    * if yes then check it's version so that to to set field type accordingly
    */

    jQuery( "#edit-body-0-format--2" ).change(function() {
        if(jQuery('#edit-body-0-format--2').val() == 'full_html' || jQuery('#edit-body-0-format--2').val() == 'basic_html'){
            for(var j = 0; j < fields.length; j++){
                var field_id = fields[j].selector.substring(0, fields[j].selector.length - 2);
                if(jQuery(field_id).attr('class').search('processed') != -1){
                    fields[j].type = fieldType;
                }
                else{
                    fields[j].type = 'FieldInput';
                }
            } /* end for */

            let map = SiteAttentionModule.FieldFactory ( fields );

            SiteAttention.load
            (
                {
                    cms : SiteAttentionModule.Cms.Demo,
                    pid: get_pid (),
                    iid: get_iid (),
                    type: get_type (),
                    lang: get_lang (),
                    user: get_user (),
                    url: get_url(),
                    published: get_published(),
                    map: map
                }
            );
            
            //console.log(" NEW ARRAY "+JSON.stringify(fields));
        }else{ // when Full Html is not selected
            for(var j = 0; j < fields.length; j++){
                if(fields[j].seo == 'content'){
                    fields[j].type = 'FieldInput';
                }
            }

            let map = SiteAttentionModule.FieldFactory ( fields );
            SiteAttention.load
            (
                {
                    cms : SiteAttentionModule.Cms.Demo,
                    pid: get_pid (),
                    iid: get_iid (),
                    type: get_type (),
                    lang: get_lang (),
                    user: get_user (),
                    url: get_url(),
                    published: get_published(),
                    map: map
                }
            );
            //console.log(" FULL HTML NOT SELECTED "+JSON.stringify(fields));
        }
    });

    if (window.CKEDITOR) { /*alert('ckeditor is enabled'); */}
    if(typeof tinymce !== 'undefined'){
        if(tinymce.majorVersion == 3){
            fieldType = 'FieldTinyMCE3';
        }else if(tinymce.majorVersion == 4){
            fieldType = 'FieldTinyMCE4';
        }
    }else{
        fieldType = 'FieldInput';
    }
    
    for (i=0; i<elements.length; i++){

        if(elements[i].type != 'hidden' && elements[i].type != 'fieldset' && elements[i].type != 'submit' && elements[i].id != 'edit-revision-log-0-value' 
            && elements[i].id != 'edit-menu-title' && elements[i].id != 'edit-menu-menu-parent' && elements[i].id != 'edit-comment-0-status-2' 
            && elements[i].id != 'edit-comment-0-status-1' && elements[i].id != 'edit-uid-0-target-id' && elements[i].id != 'edit-created-0-value-date' 
            && elements[i].id != 'edit-created-0-value-time' && elements[i].id != 'edit-promote-value' && elements[i].id != 'edit-sticky-value' && elements[i].id != 'edit-revision-log-0-value'
            && elements[i].id != 'edit-body-0-format--2' && elements[i].id != 'edit-menu-description' && elements[i].id != 'edit-menu-weight' && elements[i].id != 'edit-menu-enabled'
            ){
            // elements[i].id != 'edit-path-alias'  just to hide URL alias field
            //alert(elements[i].type + ' ' + elements[i].id);

            var field_id   = elements[i].id;
            var field_name = elements[i].name;
            var field_type = elements[i].type == 'text' || elements[i].type == 'file' ? 'FieldInput' : fieldType;
            var field_seo  = '';

            // if the text format is not set to Full Html then set field type to FieldInput
            if(jQuery('#edit-body-und-0-format--2').val() != 'full_html'){
                field_type = 'FieldInput';
            }

            if(jQuery('#'+field_id).attr('class').search('wysiwyg') == -1 && jQuery('#'+field_id).attr('class').search('processed') == -1 ){
               field_type = 'FieldInput';
            }

            if(elements[i].id == 'edit-path-alias'){
                field_seo = 'url';
            }else{
                field_seo = seo_type_arr[elements[i].type];
            }

            fields.push({
                seo: field_seo,
                name: field_name,
                selector: '#'+ field_id +'|0',
                type: field_type
            });

            if(field_seo == 'content'){ // In order to bind link field with content/body field
                content_field_id = field_id;
            }

        }
    }

    fields.push({
        seo: 'links',
        name: 'Links',
        selector: '#'+content_field_id+'|0',
        type: 'FieldInput'
    });
    


    /**
     * Create the field classes so SAPL can use them. Simply pass the
     * generated fields use the returned as a parameter for SAPL.load()
     *
     * @type {Object}
     */
    console.log(JSON.stringify(fields));
    let map = SiteAttentionModule.FieldFactory ( fields );


    /**
     * Example of defined function that is set as a hook. This particular method
     * is called after registration so the values passed by SiteAttention are
     * status: true|false if the process has run successfully and msg: the
     * license key that the registration used.
     *
     * @method alerting
     * @param  {Boolean} status Returns weather the event was successful or not.
     *                          Will always be true when time is before
     * @param  {Mixed} msg    The argument send by SiteAttention. If it is an
     *                        error, msg will contain the string, otherwise read
     *                        the documentation to see what each event returns
     * @param  {String} args   First example parameter
     * @param  {Number} args   Second example parameter
     */

    let save_client_data = function ( status, key, iid )
    {
        jQuery.ajax({
           url: Drupal.settings.SA.ajaxUrl, // This is the AjAX URL set by the SiteAttention Module
           method: "GET",
           data: { status : status, key : key, iid : iid }, // Set the data to be saved in DB
           success: function(data) {
              //alert('Key and Instance ID/Name saved');
           }

        });
    }




    /**
     * Example of setting up a hook after the resume event
     */
    SiteAttentionModule.hooks.add
    (
        'after', // timing
        'register', // event
        'saving client data', // human readable hook name
        save_client_data, // the actual callback
        this, // the context where your function will be called
        [ ] // extra arguments
    );

    let save_instance = function ( status, instance_arr )
    {
        jQuery.ajax({
           url: Drupal.settings.SA.AjaxUrlUpdateIData, // This is the AjAX URL set by the SiteAttention Module
           method: "GET",
           data: { status : status, instance_arr : instance_arr }, // Pass the data to be saved in DB
           success: function(data) {
              alert('Instance Name saved.');
           }
        });
    }

    // this hook will be called after hitting save instance button (setting form)
    SiteAttentionModule.hooks.add
    (
        'after',
        'instance', // event
        'save instance data', // human readable hook name
        save_instance, // the actual callback
        this, // the context where your function will be called
        [ ] // extra arguments
    );




    /**
     * The method that will be called when SiteAttention script has finished
     * loading.
     */

    let has_loaded = function ()
    {
        /**
         * Start SiteAttention in case it is not already started.
         */

        //SiteAttention.play();
        SiteAttention.play
        ({
            container: get_container (),
            minimised: get_minimised ()
        });

        /**
         * Load SiteAttention configuration and let it do it's job.
         */

        SiteAttention.load
        (
            {
                cms : SiteAttentionModule.Cms.Demo,
                pid: get_pid (),
                iid: get_iid (),
                type: get_type (),
                lang: get_lang (),
                user: get_user (),
                url: get_url(),
                published: get_published(),
                map: map
            }
        );
    }




    /**
     * Use the utility from SiteAttentionModule library to load the
     * SiteAttention script call the has_loaded method when it is loaded.
     */

    SiteAttentionModule.inject_script ( data.url , has_loaded );
};
//# sourceURL=cms.sa.js

// compiled data for testing
sa_test_data =
{
    "aid": "#1",
    "cid": "try_siteattention_demo",
    "type": "article",
    "lang": "en",
    "user": "Marius Jigoreanu",
    "url": "http://sa.localhost/?free#1",
    "published": true,
    "map":[
    {
        "seo": "url",
        "name": "URL",
        "selector": "#url|0",
        "type": "FieldInput"
    },
    {
        "seo": "title",
        "name": "Title",
        "selector": "#title|0",
        "type": "FieldInput"
    },
    {
        "seo": "metakeywords",
        "name": "Keywords",
        "selector": "#metakeywords|0",
        "type": "FieldInput"
    },
    {
        "seo": "metadescription",
        "name": "Description",
        "selector": "#metadescription|0",
        "type": "FieldInput"
    },
    {
        "seo": "content",
        "name": "Content",
        "selector": "#content|0",
        "type": "FieldTinyMCE4"
    },
    {
        "seo": "headers",
        "name": "Headers",
        "selector": "#content|0",
        "type": "FieldTinyMCE4"
    },
    {
        "seo": "images",
        "name": "Images",
        "selector": "#content|0",
        "type": "FieldTinyMCE4"
    },
    {
        "seo": "videos",
        "name": "Videos",
        "selector": "#content|0",
        "type": "FieldTinyMCE4"
    },
    {
        "seo": "links",
        "name": "Links",
        "selector": "#content|0",
        "type": "FieldTinyMCE4"
    } ]
};
