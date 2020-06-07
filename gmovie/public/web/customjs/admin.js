
$(document).ready(function () {
    var counter = 1;
    $('#badge-form').validate({
        rules: {
            'badge[badgeImage]': {
                required: true
            },
            'badge[badgeName]': {
                required: true
            }
        }
    });
    $('#rule-form').validate({
        rules: {
            'achievement[count]': {
                required: true
            },
            'achievement[genre]': {
                required: true
            }
        }
    });
    $('#badgeRule-form').validate({
        rules: {
            'achievement_badge[badge]': {
                required: true
            },
            'achievement_badge[rule]': {
                required: true
            }
        }
    });

    $('#rule').hide();
    $('#achievement').hide();

    $('#badge-tab').click(function () {
        $('#badge').show();
        $('#rule').hide();
        $('#achievement').hide();
    });
    $('#rule-tab').click(function () {
        $('#rule').show();
        $('#badge').hide();
        $('#achievement').hide();
    });
    $('#achievement-tab').click(function () {
        $('#achievement').show();
        $('#badge').hide();
        $('#rule').hide();
    });


    $('.add-rule').click(function () {
        var template = $.trim($('#ruleTemplate').html()).replace(/{<{counter}>}/ig, counter);
        $('#rule_container').append(
            template
        );
        $('.remove-rule').click(function () {
            $(this).closest('.rule').remove();
        });
        counter += 1;
    });

    $("#badgeTable").on("click", ".btnDelete", function () {
        var row = $(this).closest("tr");
        var badgeId = $(this).data("id");
        $.ajax({
            type: 'POST',
            url: "/badge/delete/" + badgeId,
            data: {
                badgeId: badgeId
            },
            success: function (data) {
                row.remove();
            }
        });
    });


    $("#ruleTable").on("click", ".btnDelete", function () {
        var row = $(this).closest("tr");
        var ruleId = $(this).data("id");
        $.ajax({
            type: 'POST',
            url: "/rule/delete/" + ruleId,
            data: {
                ruleId: ruleId
            },
            success: function (data) {
                row.remove();
            }
        });
    });

    $("#templateTable").on("click", ".btnDelete", function () {
        var row = $(this).closest("tr");
        var templateId = $(this).data("id");
        $.ajax({
            type: 'POST',
            url: "/template/delete/" + templateId,
            data: {
                templateId: templateId
            },
            success: function (data) {
                row.remove();
            }
        });
    });


    $("#achievementsTable").on("click", ".btnDelete", function () {
        var row = $(this).closest("tr");
        var achievementId = $(this).data("id");
        $.ajax({
            type: 'POST',
            url: "/achievement/delete/" + achievementId,
            data: {
                achievementId: achievementId
            },
            success: function (data) {
                row.remove();
            }
        });
    });

    $(".rule_toggle").find('input:first').click(function () {
        var ruleId = $(this).data("id");
        $.ajax({
            type: 'POST',
            url: "/rule/activate/" + ruleId,
            data: {
                ruleId: ruleId
            }
        });
    });

    $(".badge_toggle").find('input:first').click(function () {
        var ruleId = $(this).data("id");
        $.ajax({
            type: 'POST',
            url: "/badge/activate/" + ruleId,
            data: {
                ruleId: ruleId
            }
        });
    });
});
