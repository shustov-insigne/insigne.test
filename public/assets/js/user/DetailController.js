if ( typeof Insigne !== 'function' ) var Insigne = function () {};
if ( typeof Insigne.Test !== 'function' ) Insigne.Test = function () {};
if ( typeof Insigne.Test.User !== 'function' ) Insigne.Test.User = function () {};

Insigne.Test.User.DetailController = (function () {

    'use strict';
    
    const self = this;
    
    // datepicker defaults
    const formatParam = 'dd-mm-yyyy';
    const weekStartParam = 1;
    const maxViewModeParam = 2;
    const languageParam = 'ru';
    const orientationParam = 'bottom auto';
    
    self.ajaxUrl = null;

    self.datepicker = {
        format: null,
        weekStart: null,
        maxViewMode: null,
        language: null,
        orientation: null
    };
    
    self.submitBtn = null;
    self.loginInput = null;
    self.emailInput = null;
    self.lastNameInput = null;
    self.firstNameInput = null;
    self.secondNameInput = null;
    self.subscriptionDateInput = null;
    self.passwordInput = null;
    self.modalWindow = null;
    
    const init = function (params) {
        
        console.log(params);
        
        self.ajaxUrl = params.ajaxUrl;

        self.submitBtn = $('#' + params.submitBtnId);
        self.loginInput = $('#' + params.loginInputId);
        self.emailInput = $('#' + params.emailInputId);
        self.lastNameInput = $('#' + params.lastNameInputId);
        self.firstNameInput = $('#' + params.firstNameInputId);
        self.secondNameInput = $('#' + params.secondNameInputId);
        self.subscriptionDateInput = $('#' + params.subscriptionDateInputId);
        self.passwordInput = $('#' + params.passwordInputId);
        self.modalWindow = $('#' + params.modalId);
        
        if (params.hasOwnProperty('datepicker')) {
            self.datepicker = {
                format: params.datepicker.format,
                weekStart: params.datepicker.weekStart,
                maxViewMode: params.datepicker.maxViewMode,
                language: params.datepicker.language,
                orientation: params.datepicker.orientation
            }
        }

        initDatepicker();
        bindSubmitBtnClick();
    };

    const initDatepicker = function () {
        self.subscriptionDateInput.datepicker({
            format: self.datepicker.format || formatParam,
            weekStart: self.datepicker.weekStart || weekStartParam,
            maxViewMode: self.datepicker.maxViewMode || maxViewModeParam,
            language: self.datepicker.language || languageParam,
            orientation: self.datepicker.orientation || orientationParam,
            clearBtn: true,
            todayHighlight: true
        });
    };
    
    const bindSubmitBtnClick = function () {

        self.submitBtn.on('click', function (e) {

            $.ajax(self.ajaxUrl, {
                type: "PUT",
                dataType: "json",
                contentType: "application/json",
                processData: false,
                data: JSON.stringify({
                    "login": self.loginInput.val(),
                    "email": self.emailInput.val(),
                    "lastName": self.lastNameInput.val(),
                    "firstName": self.firstNameInput.val(),
                    "secondName": self.secondNameInput.val(),
                    "subscriptionDate": self.subscriptionDateInput.val(),
                    "password": self.passwordInput.val()
                }),
                success: function (data, textStatus, jqXHR) {

                    if (data.result === 'success') {
                        self.modalWindow.find('.modal-title').text('Информационное сообщение');
                        self.modalWindow.find('.modal-body').text('Данные успешно обновлены');
                    } else {
                        self.modalWindow.find('.modal-title').text('Ошибка');
                        self.modalWindow.find('.modal-body').text(data.description);
                    }

                    self.modalWindow.modal('show');
                },
                error: function (jqXHR, textStatus, errorThrown) {

                    self.modalWindow.find('.modal-title').text('Неизвестная ошибка');
                    self.modalWindow.find('.modal-bode').text('Неизвестная ошибка, перезагрузите страницу');
                    self.modalWindow.modal('show');
                }
            });
        });
    };
    
    return {
        'init': init
    }
});