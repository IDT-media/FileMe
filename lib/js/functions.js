/**
 *-----------------------------------------------------------------------
 * Module: Polly
 * Version: 1.0
 *-------------------------------------------------------------------------
 *
 * Authors:
 *
 * Tapio Löytty, <tapsa@orange-media.fi>
 * Web: www.orange-media.fi
 *
 * Goran Ilic, <uniqu3e@gmail.com>
 * Web: www.ich-mach-das.at
 *
 *-------------------------------------------------------------------------
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 * Or read it online: http://www.gnu.org/licenses/licenses.html*GPL
 *
 *-------------------------------------------------------------------------
 **/
;
(function (global, $) {
    'use strict';

    var Polly = global.Polly = {};
    
    Polly.version = '1.0';
    
    /**
     * Initializes needed function 
     */
    Polly.runner = function () {
        var _this = this;
        
        if ($('#polly-options-container').length > 0) {
            // Initialize UI
            _this.UI.itemSortable();
            _this.UI.addNewItem();
            _this.UI.inlineEdit();
            // load existing items
            _this.dataHandler.renderExistingItems();
        }
        
        if ($('#polly-pie-chart').length > 0) {
            // load google jsapi and draw our chart
            _this.loadScript('//www.google.com/jsapi', _this.visualisation.drawChart);
            _this.loadScript(polly_module_path + '/lib/js/jquery.throttledresize.js');
        }
    };
    
    /**
     * @description conditional load script helper function
     * @author Brad Vincent https://gist.github.com/2313262
     * @function loadScript(url, arg1, arg2)
     * @param {string} url
     * @callback requestCallback
     * @param {requestCallback|boolean} arg1
     * @param {requestCallback|boolean} arg2
     */
    Polly.loadScript = function(url, arg1, arg2) {
        var cache = true, 
            callback = null, 
            load = true;
        //arg1 and arg2 can be interchangable
        if ($.isFunction(arg1)) {
            callback = arg1;
            cache = arg2 || cache;
        } else {
            cache = arg1 || cache;
            callback = arg2 || callback;
        }
        
        //check all existing script tags in the page for the url
        $('script[type="text/javascript"]').each(function() {
            var load = ( url !== $(this).attr('src') );
            return load;
        });
        
        if (load) {
            //didn't find it in the page, so load it
            $.ajax({
                type : 'GET',
                url : url,
                async : false,
                success : callback,
                dataType : 'script',
                cache : cache
            });
        } else {
            //already loaded so just call the callback
            if ($.isFunction(callback)) {
                callback.call(this);
            }
        }
    };

    Polly.dataHandler = {
        
        /**
         * @description Build a unordered list from JSON Array
         * @function renderExistingItems() 
         */
        renderExistingItems: function () {
            
            var _this = this,
                $container = $('#polly-options'),
                $input = $('#polly-data-values'),
                data = JSON.parse(decodeURIComponent($input.val())),
                lang = $container.data('polly-lang'),
                actionid = $container.data('polly-actionid'),
                items = [];
            
            $(data).each(function (index, el) {
                
                // setup layout for items
                var $li = $('<li/>')
                        .addClass('cf polly-row')
                        .attr('data-polly-option-id', el.id)
                        .attr('data-polly-option-type', el.type)
                        .attr('data-polly-position', index),
                    $draggable = $('<span/>')
                        .addClass('polly-drag-handle')
                        .attr('title', lang.sort_drag)
                        .appendTo($li)
                        .append($('<span/>').addClass('visuallyhidden').text(lang.sort_drag)),
                    $a = $('<a/>')
                        .text(el.data)
                        .addClass('polly-editable editable-click polly-question-text')
                        .attr('data-type', 'text')
                        .attr('data-title', lang.sort_drag)
                        .appendTo($li),
                    $delete = $('<span/>')
                        .addClass('polly-remove')
                        .attr('title', lang.delete)
                        .appendTo($li)
                        .append($('<i/>').attr('aria-hidden', 'true').addClass('polly-ico-remove'));

                items.push($li);
            });

            // append all items to parent UL
            $container.append.apply($container, items);
            
            _this.deleteItem();
            _this.saveItemValues();
        },
        
        /**
         * @description Handles deleting of a item, fades out parent LI and updates JSON Array
         * @function deleteItem()
         *  
         */
        deleteItem: function () {
            
            var _this = this,
                $trigger = $('.polly-remove');
            
            $trigger.on('click', function () {
                $(this).parent('li').fadeOut('fast', function () {
                    
                    // remove element and update JSON Array
                    $(this).remove();
                    _this.updateJSONArray();
                });
            });
        },
        
        /**
         * @description Updates data-polly-position data attribute with new index number
         * @function updateItemsPosition() 
         */
        updateItemsPosition: function () {
            
            var _this = this,
                $container = $('#polly-options'),
                $items = $container.children();
                
                $items.each(function (index, el) {
                    $(el).attr('data-polly-position', index);
                });
            
        },
        
        /**
         * @description Updates JSON Array based on x-editable hidden Event, which is triggered each time a editable is saved or closed
         * @function saveItemValues() 
         * @requires x-Editable plugin
         */
        saveItemValues: function () {
            
            var _this = this;
            
            $('.polly-editable').on('click', function(event) {
                
                event.preventDefault();
                
                $(this).on('hidden', function (e, reason) {
                    _this.updateJSONArray();
                });
            });
        },
        
        /**
         * @description updates JSON Array and hidden input value for submission
         * @function updateJSONArray()
         * @requires jQuery JSON plugin 
         */
        updateJSONArray: function () {
            
            var $container = $('#polly-options'),
                $items = $container.children(),
                $input = $('#polly-data-values'),
                data = [];
                
                $items.each(function (index, el) {
                    var $this = $(el),
                        $data = $this.data(),
                        values = {};
                        
                    values.id = $data.pollyOptionId;
                    values.type = $data.pollyOptionType;
                    values.position = $data.pollyPosition;
                    values.data = $this.find('a').text();
                    
                    if ((values.data !== 'Empty') && (values.data !== 'Insert question')) {
                        data.push(values);
                    }
                    
                });
            
            $input.val(encodeURIComponent(JSON.stringify(data)));
        }
    };

    Polly.UI = {
        
        /**
         * @description Initializes jQuery UI sortable function and updates items Position and JSON Array 
         * @function itemSortable()
         * @requires jQuery UI
         */
        itemSortable: function () {
            $('#polly-options').sortable({
                helper: function (e, el) {
                    // element while sorting
                    var $helper = $('<div class="polly-drag-helper">' + $(el).find('.polly-question-text').text() + '</div>');

                    $helper.appendTo('body');
                    return $helper;
                },
                update: function (event, ui) {
                    // update needed values
                    Polly.dataHandler.updateItemsPosition();
                    Polly.dataHandler.updateJSONArray();
                }
            });
        },
        
        /**
         * @description Handles adding new LI element for a new option item
         * @function addNewItem() 
         */
        addNewItem: function () {

            var $container = $('#polly-options'),
                lang = $container.data('polly-lang'),
                actionid = $container.data('polly-actionid');

            $('#polly-add-new').button({
                icons: {
                    primary: 'ui-icon-circle-plus'
                }
            }).click(function (e) {

                var $trigger = $(this),
                    $items = $container.children(),
                    count = $items.length,
                    $li = $('<li/>')
                        .addClass('cf polly-row')
                        .attr('data-polly-option-id', '')
                        .attr('data-polly-option-type', 'PreDefined')
                        .attr('data-polly-position', count),
                    $draggable = $('<span/>')
                        .addClass('polly-drag-handle')
                        .attr('title', lang.sort_drag)
                        .appendTo($li)
                        .append($('<span/>').addClass('visuallyhidden').text(lang.sort_drag)),
                    $a = $('<a/>')
                        .addClass('polly-editable editable-click editable-empty polly-question-text')
                        .text(lang.insert_question)
                        .attr('data-type', 'text')
                        .attr('data-title', lang.sort_drag)
                        .appendTo($li),
                    $delete = $('<span/>')
                        .addClass('polly-remove')
                        .attr('title', lang.delete)
                        .appendTo($li)
                        .append($('<i/>').attr('aria-hidden', 'true').addClass('polly-ico-remove'));

                $container.append($li);
                
                Polly.dataHandler.deleteItem();
                Polly.dataHandler.saveItemValues();

                e.preventDefault();
            });
        },

        /**
         * @description Initializes x-editable jQuery plugin for inline editing of option values
         * @function inlineEdit()
         * @requires x-editable plugin 
         */
        inlineEdit: function () {

            $.fn.editable.defaults.mode = 'inline';
            $('#polly-options').editable({
                placement: 'right',
                selector: 'a'
            });
        }
    },
    
    Polly.visualisation = {

        /**
         * @description Loads Google Charts API and draws a pie chart based on options and vote counts
         * @function drawChart()
         * @requires throttledresize plugin 
         * @requires Google jsapi
         */
        drawChart : function() {

            if (typeof google !== 'undefined' && google && google.load) {
                
                // load google charts
                google.load('visualization', '1', {packages:['corechart'], 'callback' : ''});
                
                $(document).on('click', '#statistics', function() {
                    
                    var data = new google.visualization.DataTable(),
                        chart = new google.visualization.PieChart(document.getElementById('polly-pie-chart')),
                        options = {
                            //is3D: true,
                            pieHole: 0.2,
                            minorTicks: 5,
                            animation: {
                                duration: 2000,
                                easing:'in'
                            }
                        };
                    
                    // set chart data
                    data.addColumn('string', 'Option');
                    data.addColumn('number', 'Votes');
                    data.addRows(votes);
                    // draw the chart
                    chart.draw(data, options);
                    
                    // redraw our chart on window resize, using throttledresize and orientaitionchange events to reduce number of redraws in chrome and safari
                    $(window).on('throttledresize orientationchange', function( event ) {
                        chart.draw(data, options);
                    });
                });
            }
        }
    };

    $(document).ready(function () {
        Polly.runner();
    });

}(this, jQuery));
