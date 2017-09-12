/**
 * Created by Ive on 18/01/2017.
 */
(function() {
    function getSchedule(uid, success) {
        $.ajax({
            url: 'api/schedule/' + uid,
            type: 'GET',
            // async: false,
            timeout: 2000,
            success: function(data) {
                success(data);
            },
            error: function(response, status, error) {
                alert(error.responseText);
            }
        });
    }

    var tdid; // id for td
    // 查看课表
    $('button[data-show-schedule]').click(function() {
        var uid = $(this).attr('data-show-schedule');
        getSchedule(uid, function(data) {
            var tbody = $('div.modal-body:eq(0)').find('tbody');
            for (var i = 0; i < data.length; i++) {
                for (var j = 0; j < data[i].length; j++) {
                    tdid = i + 1;
                    tbody.find('tr:eq(' + j + ')').find('td:eq(' + tdid + ')').html(data[i][j]);
                }
            }
        });
    });
    // 修改课表
    var tmpId;
    $('button[data-change-schedule]').click(function() {
        var uid = $(this).attr('data-change-schedule');
        tmpId = uid;
        getSchedule(uid, function(data) {
            var tbody = $('div.modal-body:eq(1)').find('tbody');
            for (var i = 0; i < data.length; i++) {
                for (var j = 0; j < data[i].length; j++) {
                    var temp = data[i][j];
                    switch (temp) {
                        case '没课':
                            temp = 0;
                            break;
                        case '有课':
                            temp = 1;
                            break;
                        case '双周有课':
                            temp = 2;
                            break;
                        case '单周有课':
                            temp = 3;
                            break;
                    }
                    tdid = i + 1;
                    tbody.find('tr:eq(' + j + ')').find('td:eq(' + tdid + ')').find('select').val(temp);
                }
            }
        });
    });
    // 保存课表
    $('button[data-sava-schedule]').click(function() {
        var schedule = [],
            tbody = $('table[data-schedule-select]').find('tbody');
        for (var i = 0; i < 5; i++) {
            tdid = i + 1;
            schedule[i] = [];
            for (var j = 0; j < 6; j++) {
                schedule[i][j] = parseInt(tbody.find('tr:eq(' + j + ')').find('td:eq(' + tdid + ')').find('select').val());
            }
        }
        var jsondata = {
            "idcard": tmpId,
            "schedule": JSON.stringify(schedule)
        };
        $.ajax({
            url: 'api/updateSchedule',
            type: 'POST',
            // async: false,
            data: jsondata,
            success: function(data) {
                // console.log('Update schedule successfull!');
                console.log(jsondata);
                console.log(data);
            },
            error: function(response, status, error) {
                alert(error.responseText);
            }
        });
        $('#myChanS').modal('hide');
    });

    // 删除人员
    $('button[data-del-user]').click(function() {
        var _this = $(this);
        var uid = _this.attr('data-del-user');
        $.ajax({
            url: 'api/del/' + uid,
            type: 'GET',
            timeout: 2000,
            success: function(data) {
                _this.parent().parent().remove();
            },
            error: function(response, status, error) {
                alert(error.responseText);
            }
        });
    });

    // 更新配置
    $('.config-switch').click(function() {
        $.ajax({
            url: 'api/updateConfig/' + $(this).attr('data-toggle'),
            success: function(data) {
                // console.log(data);
            }
        });
    });
    $('input[data-type=config]').blur(function() {
        // console.log('xiao yi !!');
        $.ajax({
            url: 'api/updateConfig/' + $(this).attr('data-toggle') + '/' + $(this).val(),
            success: function() {
                console.log('更新配置成功');
            },
            error: function(e) {
                alert('更新配置失败');
            }
        })
    });

    // 生成课表
    $('input[data-type=generate]').click(function() {
        var $btn = $(this).button('loading');
        $.ajax({
            url: 'api/generate',
            success: function() {
                location.href = 'duty';
                $btn.button('reset');
            }
        })
    });

    // Info页面搜索
    $('#filter-text').keyup(function() {
        var txt = $('#filter-text').val();
        $("table tbody tr")
            .hide()
            .filter(":contains('" + txt + "')")
            .show();
    })
})();