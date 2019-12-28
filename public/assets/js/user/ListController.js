if ( typeof Insigne !== 'function' ) var Insigne = function () {};
if ( typeof Insigne.Test !== 'function' ) Insigne.Test = function () {};
if ( typeof Insigne.Test.User !== 'function' ) Insigne.Test.User = function () {};

Insigne.Test.User.ListController = (function () {

    'use strict';

    const self = this;

    const pagesToCache = 5;
    const pageLength = 50;

    self.ajaxUrl = null;
    self.languageUrl = null;

    self.tableCard = null;
    self.table = null;
    self.idInput = null;
    self.loginInput = null;
    self.fullNameInput = null;
    self.emailInput = null;

    self.userDetailPathTemplate = null;

    self.columns = [];
    
    self.dataTable = null;
    

    const init = function (params) {

        self.ajaxUrl = params.ajaxUrl;
        self.languageUrl = params.languageUrl;

        self.columns = prepareColumns(params.columns);

        self.tableCard = $('#' + params.tableCardId);
        self.table = $('#' + params.tableId);
        self.idInput = $('#' + params.idInputId);
        self.loginInput = $('#' + params.loginInputId);
        self.fullNameInput = $('#' + params.fullNameInputId);
        self.emailInput = $('#' + params.emailInputId);

        self.userDetailPathTemplate = params.userDetailPathTemplate;

        initTable();
        bindSearchBtnClick(params.searchBtnId);
        bindClearBtnClick(params.clearBtnId);
        bindRowDbClick();
    };

    const initTable = function () {

        // самый корректный способ скрыть стандартное поле "поиск"
        var domParamValue = "<'row'<'col-12 col-lg-6'l><'col-12 col-lg-6'>>" +
            "<'row'<'col-12'tr>>" +
            "<'row'<'col-12 col-lg-5'i><'col-12 col-lg-7'p>>";

        self.dataTable = self.table
            .on('processing.dt', function (e, settings, processing) {
                self.tableCard.toggleClass('disabled-block', processing)
            })
            .DataTable({
                searching: true,
                pageLength: pageLength,
                processing: true,
                serverSide: true,
                ajax: InsigneTest.DataTables.Pipeline({
                    url: self.ajaxUrl,
                    type: "POST",
                    dataType: "json",
                    pages: pagesToCache, // number of pages to cache
                    data: function (data) {
                        data.customSearch = {
                            id: self.idInput.val(),
                            login: self.loginInput.val(),
                            fullName: self.fullNameInput.val(),
                            email: self.emailInput.val()
                        }
                    }
                }),
                columns: self.columns,
                language: {
                    "url": self.languageUrl
                },
                search: {
                    caseInsensitive: false,
                    regex: false,
                    smart: false
                },
                dom: domParamValue
            });
    };

    const prepareColumns = function (columns) {
        for (var index = 0; index < columns.length; index++) {
            if (columns[index].data === 'id') {
                columns[index].render = function(data, type, row, meta) {
                    if (type === 'display') {
                        data = '<a href="' + self.userDetailPathTemplate.replace('user_id', row.id) + '">' + data + '</a>';
                    }

                    return data;
                }
            }
        }

        return columns;
    };

    const bindSearchBtnClick = function (searchBtnId) {
        $('#' + searchBtnId).on('click', function () {
            // TODO: подумать, можно ли убрать этот костыль
            self.dataTable.search(Date.now()).draw();
        });
    };

    const bindClearBtnClick = function (clearBtnId) {
        $('#' + clearBtnId).on('click', function () {
            self.idInput.val('');
            self.loginInput.val('');
            self.fullNameInput.val('');
            self.emailInput.val('');
            // TODO: подумать, можно ли убрать этот костыль
            self.dataTable.search(Date.now()).draw();
        });
    };
    
    const bindRowDbClick = function () {
        self.table.find('tbody').on('dblclick', 'tr', function () {
            var data = self.dataTable.row(this).data();
            window.location.href = self.userDetailPathTemplate.replace('user_id', data.id);
        });
    };

    return {
        'init': init
    }
});