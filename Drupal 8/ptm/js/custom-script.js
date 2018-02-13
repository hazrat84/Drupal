/**
 * @file
 */

equalheight = function (container) {

    var currentTallest = 0,
        currentRowStart = 0,
        rowDivs = new Array(),
        jQueryel,
        topPosition = 0;
    jQuery(container).each(function () {

        jQueryel = jQuery(this);
        jQuery(jQueryel).height('auto')
        topPostion = jQueryel.position().top;

        if (currentRowStart != topPostion) {
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].height(currentTallest);
            }
            rowDivs.length = 0; // Empty the array.
            currentRowStart = topPostion;
            currentTallest = jQueryel.height();
            rowDivs.push(jQueryel);
        }
else {
            rowDivs.push(jQueryel);
            currentTallest = (currentTallest < jQueryel.height()) ? (jQueryel.height()) : (currentTallest);
        }
        for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
            rowDivs[currentDiv].height(currentTallest);
        }
    });
}

jQuery(window).load(function () {
    equalheight('.main h5 ');
});


jQuery(window).resize(function () {
    equalheight('.main h5 ');
});

jQuery(document).ready(function () {
    "use strict";
    jQuery(".welcome-to-people-teachme").click(function () {
        jQuery("#landing-page1").dialog({
            modal: true,
            "width": 900 ,

        });
    });
    jQuery(".signup").click(function () {
        jQuery("#signup").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".login").click(function () {
        jQuery("#login").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".signup-unfolded").click(function () {
        jQuery("#signup-unfolded").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".login-forgot-password").click(function () {
        jQuery("#login-forgot-password").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".update-password").click(function () {
        jQuery("#update-password").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".update-password2").click(function () {
        jQuery("#update-password").dialog("close");
        jQuery("#update-password2").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".update-email").click(function () {
        jQuery("#update-email").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    jQuery(".update-email2").click(function () {
        jQuery("#update-email").dialog('close');
        jQuery("#update-email2").dialog({
            modal: true,
            "width": 400 ,

        });
    });

    var footerClass = jQuery(".footer").attr('class');
    jQuery('body').addClass(footerClass);

    jQuery('body').removeClass('container-fluid');
    jQuery('body').removeClass('footer');
    jQuery('body').removeClass('footer1');

    function toggleIcon(e) {
        jQuery(e.target)
            .prev('.panel-heading')
            .find(".more-less")
            .toggleClass('glyphicon-plus glyphicon-minus');
    }
    jQuery('.panel-group').on('hidden.bs.collapse', toggleIcon);
    jQuery('.panel-group').on('shown.bs.collapse', toggleIcon);
    learnDropDown();
});
/*learn DropDown*/
jQuery(window).resize(function () {
    "use strict";
    learnDropDown();
});
/*learn DropDown*/
function learnDropDown(){
    "use strict";
    if (jQuery(window).width() > 767) {
        jQuery(".learnToggle").click(function () {
            jQuery(".learnDropDown").toggle();
            jQuery(".learnToggle").toggleClass('showLearn');
        });
        jQuery(document).mouseup(function (e) {
            var learncontainer = jQuery(".learnDropDown");
            if (!learncontainer.is(e.target) && learncontainer.has(e.target).length === 0) {
                learncontainer.hide();
                jQuery(".learnToggle").removeClass('showLearn');
            }
        });
    }
else {
        jQuery(".learnToggle").click(function () {
            jQuery(".learnDropDown").css("display","");
        });
        jQuery(document).mouseup(function (e) {
            var learncontainer = jQuery(".learnDropDown");
            if (!learncontainer.is(e.target) && learncontainer.has(e.target).length === 0) {
                learncontainer.css("display","");
            }
        });
    }
    if (jQuery(window).width() > 767) {
        jQuery(".learnDropDown").css("display","none");
        jQuery(".learnDropDown").insertAfter(jQuery(".logo"));
    }
else {
        jQuery(".learnDropDown").css("display","");
        jQuery(".learnDropDown").insertAfter(jQuery(".learnToggle"));
    }
}
/*learn DropDown*/
/*learn DropDown*/
/*Slider*/
/*Slider*/
jQuery(".slider1").owlCarousel({
    slideSpeed: 1000,
    navigation: true,
    pagination: false,
    items: 6,
    itemsDesktop: [1199, 6],
    itemsDesktopSmall: [979, 5],
    itemsTablet: [768, 4],
    itemsMobile: [479, 2],
});
jQuery(".benefitsSlider").owlCarousel({
    slideSpeed: 1000,
    navigation: true,
    pagination: false,
    items: 3,
    itemsDesktop: [1199, 3],
    itemsDesktopSmall: [990, 2],
    itemsTablet: [767, 1],
    itemsMobile: [479, 1],
});
jQuery(".mainSlider").owlCarousel({
    slideSpeed: 1000,
    navigation: true,
    pagination: false,
    singleItem : true,
    itemsDesktop: [1199, 3],
    itemsDesktopSmall: [990, 2],
    itemsTablet: [767, 1],
    itemsMobile: [479, 1],
});
/*Slider*/
/*MainNav*/
jQuery(".risponsiveNav").click(function () {
    if (jQuery(".risponsiveNav").hasClass('open')) {
        jQuery(".risponsiveNav").removeClass('open');
        jQuery(".navbar-nav").removeClass('open');
        jQuery("body").css("overflow", "auto");
    }
else {
        jQuery(".risponsiveNav").addClass('open');
        jQuery(".navbar-nav").addClass('open');
        jQuery("body").css("overflow", "hidden");
    }
});
jQuery(document).mouseup(function (e) {
    var container = jQuery(".header");
    if (!container.is(e.target) && container.has(e.target).length === 0) {
        jQuery(".risponsiveNav").removeClass('open');
        jQuery(".navbar-nav").removeClass('open');
        jQuery("body").css("overflow", "auto");
    }
});
/*MainNav*/
/*SideBar*/
jQuery(".risponsivesideBar").click(function () {
    if (jQuery(".risponsivesideBar").hasClass('open')) {
        jQuery(".risponsivesideBar").removeClass('open');
        jQuery(".sideNav").removeClass('open');
        jQuery("body").css("overflow", "auto");
    }
else {
        jQuery(".risponsivesideBar").addClass('open');
        jQuery(".sideNav").addClass('open');
        jQuery("body").css("overflow", "hidden");
    }
});
/*SideBar*/
/*Change password*/
jQuery(".sessionPage .span-right a.chng-paswrd").click(function () {
    jQuery("#change_password").dialog({
        modal: true,
        "width": 400 ,
        buttons: {
            "UPDATE": function () {
                jQuery(this).dialog("close");
            }
        }
    });
});
/*SideBar*/
/*Change email*/
jQuery(".sessionPage .span-right a.chng-email").click(function () {
    jQuery("#change_email").dialog({
        modal: true,
        "width": 400 ,
        buttons: {
            "UPDATE": function () {
                jQuery(this).dialog("close");
            }
        }
    });
});

/*SideBar*/
/*succueful*/
jQuery(".form-group .half label").click(function () {
    jQuery("#succueful").dialog({
        modal: true,
        "width": 400 ,
        buttons: {
            "UPDATE": function () {
                jQuery(this).dialog("close");
            }
        }
    });
});

/*Landng page*/

/*SideBar*/
/*Creat Time slot*/
jQuery(".creatTimeSlot").click(function () {
    jQuery("#creatTime").dialog({
        modal: true,
        "width": 350 ,
        buttons: {
            "Save time slot": function () {
                jQuery(this).dialog("close");
            }
        }
    });
});



/*Creat Time slot*/
/*Edit Time slot*/
jQuery(".editTime").click(function () {
    jQuery("#editTimeSlot").dialog({
        modal: true,
        "width": 350 ,
        buttons: {
            "Save changes": function () {
                jQuery(this).dialog("close");
            },
            "Delet time slot": function () {
                jQuery(this).dialog("close");
            }
        }
    });
});

/*Edit Time slot*/
jQuery(".delete-timeslots").click(function (e) {
    e.preventDefault();

    var checkboxes = jQuery('.delete-checkboxes:checked');
    var ids = new Array();
    // console.log(checkboxes);
    jQuery.each(checkboxes, function (key, checkbox) {
        ids[key] = jQuery(checkbox).val();
    });

    if (ids.length > 0) {

        bootbox.confirm("Are you sure want to delete?", function (result) {
            if (result == true) {
                jQuery.ajax({
                    url: Drupal.url("timeslots/multipledelete"),
                    type: "POST",
                    data: { id : ids },
                    //dataType: "json"
                    success: function (response) {

                        if (response.success === 1) {
                            // alert(response);
                            window.location.reload();
                        }
                    }
                });
            }
        });

    }
    else {
        bootbox.alert("Select at least one Timeslot to delete");
    }
});
