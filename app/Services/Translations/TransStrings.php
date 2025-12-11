<?php

namespace Webmakerr\App\Services\Translations;
class TransStrings
{
    public static function getStrings(): array
    {
        $translations = require 'admin-translation.php';
        return webmakerr_apply_filters("webmakerr_cart/admin_translations", $translations, []);
    }

    public static function blockStrings(): array
    {
        $translations = require 'block-editor-translation.php';
        return webmakerr_apply_filters("webmakerr_cart/blocks_translations", $translations, []);
    }


    public static function getShopAppBlockEditorString(): array
    {

        return [
            'Also search in Content'            => _x('Also search in Content', 'Shop App Block Editor', 'webmakerr-cart'),
            'Apply Filter'                      => _x('Apply Filter', 'Shop App Block Editor', 'webmakerr-cart'),
            'Add to Cart'                       => _x('Add to Cart', 'Shop App Block Editor', 'webmakerr-cart'),
            'Default'                           => _x('Default', 'Shop App Block Editor', 'webmakerr-cart'),
            'Display Name For Filter'           => _x('Display Name For Filter', 'Shop App Block Editor', 'webmakerr-cart'),
            'Enable Default Filtering'          => _x('Enable Default Filtering', 'Shop App Block Editor', 'webmakerr-cart'),
            'Enable Filtering'                  => _x('Enable Filtering', 'Shop App Block Editor', 'webmakerr-cart'),
            'Enable'                            => _x('Enable', 'Shop App Block Editor', 'webmakerr-cart'),
            'Enabled?'                          => _x('Enabled?', 'Shop App Block Editor', 'webmakerr-cart'),
            'Filter Option'                     => _x('Filter Option', 'Shop App Block Editor', 'webmakerr-cart'),
            'Grid'                              => _x('Grid', 'Shop App Block Editor', 'webmakerr-cart'),
            'List'                              => _x('List', 'Shop App Block Editor', 'webmakerr-cart'),
            'Numbers'                           => _x('Numbers', 'Shop App Block Editor', 'webmakerr-cart'),
            'Option'                            => _x('Option', 'Shop App Block Editor', 'webmakerr-cart'),
            'Paginator'                         => _x('Paginator', 'Shop App Block Editor', 'webmakerr-cart'),
            'Per Page'                          => _x('Per Page', 'Shop App Block Editor', 'webmakerr-cart'),
            'Product Box Grid Size'             => _x('Product Box Grid Size', 'Shop App Block Editor', 'webmakerr-cart'),
            'Product Categories'                => _x('Product Categories', 'Shop App Block Editor', 'webmakerr-cart'),
            'Product Grid Size'                 => _x('Product Grid Size', 'Shop App Block Editor', 'webmakerr-cart'),
            'Product Types'                     => _x('Product Types', 'Shop App Block Editor', 'webmakerr-cart'),
            'Product'                           => _x('Product', 'Shop App Block Editor', 'webmakerr-cart'),
            'Range Filter Only works in pages.' => _x('Range Filter Only works in pages.', 'Shop App Block Editor', 'webmakerr-cart'),
            'Scroll'                            => _x('Scroll', 'Shop App Block Editor', 'webmakerr-cart'),
            'Search Grid Size'                  => _x('Search Grid Size', 'Shop App Block Editor', 'webmakerr-cart'),
            'Search'                            => _x('Search', 'Shop App Block Editor', 'webmakerr-cart'),
            'View mode'                         => _x('View mode', 'Shop App Block Editor', 'webmakerr-cart'),
            'Wildcard Filter'                   => _x('Wildcard Filter', 'Shop App Block Editor', 'webmakerr-cart'),

            'Primary'                => _x('Primary', 'Shop App Block Editor', 'webmakerr-cart'),
            'Product Heading'        => _x('Product Heading', 'Shop App Block Editor', 'webmakerr-cart'),
            'Text'                   => _x('Text', 'Shop App Block Editor', 'webmakerr-cart'),
            'Border'                 => _x('Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Badge Count Background' => _x('Badge Count Background', 'Shop App Block Editor', 'webmakerr-cart'),
            'Badge Count'            => _x('Badge Count', 'Shop App Block Editor', 'webmakerr-cart'),
            'Badge Count Border'     => _x('Badge Count Border', 'Shop App Block Editor', 'webmakerr-cart'),


            'Background'                => _x('Background', 'Shop App Block Editor', 'webmakerr-cart'),
            'Input Border'              => _x('Input Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Input Focus Border'        => _x('Input Focus Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Heading'                   => _x('Heading', 'Shop App Block Editor', 'webmakerr-cart'),
            'Label'                     => _x('Label', 'Shop App Block Editor', 'webmakerr-cart'),
            'Item Border'               => _x('Item Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Reset Button Bg'           => _x('Reset Button Bg', 'Shop App Block Editor', 'webmakerr-cart'),
            'Reset Button'              => _x('Reset Button', 'Shop App Block Editor', 'webmakerr-cart'),
            'Reset Button Border'       => _x('Reset Button Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Reset Button Hover Bg'     => _x('Reset Button Hover Bg', 'Shop App Block Editor', 'webmakerr-cart'),
            'Reset Button Hover'        => _x('Reset Button Hover', 'Shop App Block Editor', 'webmakerr-cart'),
            'Reset Button Hover Border' => _x('Reset Button Hover Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkbox'                  => _x('Checkbox', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkbox Active'           => _x('Checkbox Active', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkmark Bg'              => _x('Checkmark Bg', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkmark Border'          => _x('Checkmark Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkmark Active Bg'       => _x('Checkmark Active Bg', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkmark Active Border'   => _x('Checkmark Active Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Checkmark After Border'    => _x('Checkmark After Border', 'Shop App Block Editor', 'webmakerr-cart'),
            'Range Slider Connect Bg'   => _x('Range Slider Connect Bg', 'Shop App Block Editor', 'webmakerr-cart'),

        ];
    }

    public static function getCustomerProfileString(): array
    {
        $translations = require 'customer-profile-translation.php';
        return webmakerr_apply_filters("webmakerr_cart/customer_profile_translations", $translations, []);
    }

    public static function singleProductPageString(): array
    {
        return [
            'In Stock'     => _x('In Stock', 'Single Product Page', 'webmakerr-cart'),
            'Out Of Stock' => _x('Out Of Stock', 'Single Product Page', 'webmakerr-cart'),
        ];
    }

    public static function checkoutPageString()
    {
        $translations = require 'checkout-translation.php';
        return webmakerr_apply_filters("webmakerr_cart/checkout_translations", $translations, []);
    }

    public static function paymentsString()
    {
        $translations = require 'payments-translation.php';
        return webmakerr_apply_filters("webmakerr_cart/payments_translations", $translations, []);
    }

    public static function elStrings(): array
    {
        return [
            'name' => get_locale(),
            'el'   => [
                'breadcrumb'  => [
                    'label' => __('Breadcrumb', 'webmakerr-cart'),
                ],
                'colorpicker' => [
                    'confirm'              => __('OK', 'webmakerr-cart'),
                    'clear'                => __('Clear', 'webmakerr-cart'),
                    'defaultLabel'         => __('color picker', 'webmakerr-cart'),
                    'description'          => __('current color is {color}. press enter to select a new color.', 'webmakerr-cart'),
                    'alphaLabel'           => __('pick alpha value', 'webmakerr-cart'),
                    'alphaDescription'     => __('alpha {alpha}, current color is {color}', 'webmakerr-cart'),
                    'hueLabel'             => __('pick hue value', 'webmakerr-cart'),
                    'hueDescription'       => __('hue {hue}, current color is {color}', 'webmakerr-cart'),
                    'svLabel'              => __('pick saturation and brightness value', 'webmakerr-cart'),
                    'svDescription'        => __('saturation {saturation}, brightness {brightness}, current color is {color}', 'webmakerr-cart'),
                    'predefineDescription' => __('select {value} as the color', 'webmakerr-cart'),
                ],
                'datepicker'  => [
                    'now'              => __('Now', 'webmakerr-cart'),
                    'today'            => __('Today', 'webmakerr-cart'),
                    'cancel'           => __('Cancel', 'webmakerr-cart'),
                    'clear'            => __('Clear', 'webmakerr-cart'),
                    'confirm'          => __('OK', 'webmakerr-cart'),
                    'dateTablePrompt'  => __('Use the arrow keys and enter to select the day of the month', 'webmakerr-cart'),
                    'monthTablePrompt' => __('Use the arrow keys and enter to select the month', 'webmakerr-cart'),
                    'yearTablePrompt'  => __('Use the arrow keys and enter to select the year', 'webmakerr-cart'),
                    'selectedDate'     => __('Selected date', 'webmakerr-cart'),
                    'selectDate'       => __('Select date', 'webmakerr-cart'),
                    'selectTime'       => __('Select time', 'webmakerr-cart'),
                    'startDate'        => __('Start Date', 'webmakerr-cart'),
                    'startTime'        => __('Start Time', 'webmakerr-cart'),
                    'endDate'          => __('End Date', 'webmakerr-cart'),
                    'endTime'          => __('End Time', 'webmakerr-cart'),
                    'prevYear'         => __('Previous Year', 'webmakerr-cart'),
                    'nextYear'         => __('Next Year', 'webmakerr-cart'),
                    'prevMonth'        => __('Previous Month', 'webmakerr-cart'),
                    'nextMonth'        => __('Next Month', 'webmakerr-cart'),
                    'year'             => __('', 'webmakerr-cart'),
                    'month1'           => __('January', 'webmakerr-cart'),
                    'month2'           => __('February', 'webmakerr-cart'),
                    'month3'           => __('March', 'webmakerr-cart'),
                    'month4'           => __('April', 'webmakerr-cart'),
                    'month5'           => __('May', 'webmakerr-cart'),
                    'month6'           => __('June', 'webmakerr-cart'),
                    'month7'           => __('July', 'webmakerr-cart'),
                    'month8'           => __('August', 'webmakerr-cart'),
                    'month9'           => __('September', 'webmakerr-cart'),
                    'month10'          => __('October', 'webmakerr-cart'),
                    'month11'          => __('November', 'webmakerr-cart'),
                    'month12'          => __('December', 'webmakerr-cart'),
                    'weeks'            => [
                        'sun' => __('Sun', 'webmakerr-cart'),
                        'mon' => __('Mon', 'webmakerr-cart'),
                        'tue' => __('Tue', 'webmakerr-cart'),
                        'wed' => __('Wed', 'webmakerr-cart'),
                        'thu' => __('Thu', 'webmakerr-cart'),
                        'fri' => __('Fri', 'webmakerr-cart'),
                        'sat' => __('Sat', 'webmakerr-cart'),
                    ],
                    'weeksFull'        => [
                        'sun' => __('Sunday', 'webmakerr-cart'),
                        'mon' => __('Monday', 'webmakerr-cart'),
                        'tue' => __('Tuesday', 'webmakerr-cart'),
                        'wed' => __('Wednesday', 'webmakerr-cart'),
                        'thu' => __('Thursday', 'webmakerr-cart'),
                        'fri' => __('Friday', 'webmakerr-cart'),
                        'sat' => __('Saturday', 'webmakerr-cart'),
                    ],
                    'months'           => [
                        'jan' => __('Jan', 'webmakerr-cart'),
                        'feb' => __('Feb', 'webmakerr-cart'),
                        'mar' => __('Mar', 'webmakerr-cart'),
                        'apr' => __('Apr', 'webmakerr-cart'),
                        'may' => __('May', 'webmakerr-cart'),
                        'jun' => __('Jun', 'webmakerr-cart'),
                        'jul' => __('Jul', 'webmakerr-cart'),
                        'aug' => __('Aug', 'webmakerr-cart'),
                        'sep' => __('Sep', 'webmakerr-cart'),
                        'oct' => __('Oct', 'webmakerr-cart'),
                        'nov' => __('Nov', 'webmakerr-cart'),
                        'dec' => __('Dec', 'webmakerr-cart'),
                    ],
                ],
                'inputNumber' => [
                    'decrease' => __('decrease number', 'webmakerr-cart'),
                    'increase' => __('increase number', 'webmakerr-cart'),
                ],
                'select'      => [
                    'loading'     => __('Loading', 'webmakerr-cart'),
                    'noMatch'     => __('No matching data', 'webmakerr-cart'),
                    'noData'      => __('No data', 'webmakerr-cart'),
                    'placeholder' => __('Select', 'webmakerr-cart'),
                ],
                'mention'     => [
                    'loading' => __('Loading', 'webmakerr-cart'),
                ],
                'dropdown'    => [
                    'toggleDropdown' => __('Toggle Dropdown', 'webmakerr-cart'),
                ],
                'cascader'    => [
                    'noMatch'     => __('No matching data', 'webmakerr-cart'),
                    'loading'     => __('Loading', 'webmakerr-cart'),
                    'placeholder' => __('Select', 'webmakerr-cart'),
                    'noData'      => __('No data', 'webmakerr-cart'),
                ],
                'pagination'  => [
                    'goto'           => __('Go to', 'webmakerr-cart'),
                    'pagesize'       => __('/page', 'webmakerr-cart'),
                    'total'          => __('Total {total}', 'webmakerr-cart'),
                    'pageClassifier' => __('', 'webmakerr-cart'),
                    'page'           => __('Page', 'webmakerr-cart'),
                    'prev'           => __('Go to previous page', 'webmakerr-cart'),
                    'next'           => __('Go to next page', 'webmakerr-cart'),
                    'currentPage'    => __('page {pager}', 'webmakerr-cart'),
                    'prevPages'      => __('Previous {pager} pages', 'webmakerr-cart'),
                    'nextPages'      => __('Next {pager} pages', 'webmakerr-cart'),
                    //'deprecationWarning' => __('Deprecated usages detected, please refer to the el-pagination documentation for more details', 'webmakerr-cart'),
                ],
                'dialog'      => [
                    'close' => __('Close this dialog', 'webmakerr-cart'),
                ],
                'drawer'      => [
                    'close' => __('Close this drawer', 'webmakerr-cart'),
                ],
                'messagebox'  => [
                    'title'   => __('Message', 'webmakerr-cart'),
                    'confirm' => __('OK', 'webmakerr-cart'),
                    'cancel'  => __('Cancel', 'webmakerr-cart'),
                    'error'   => __('Illegal input', 'webmakerr-cart'),
                    'close'   => __('Close this dialog', 'webmakerr-cart'),
                ],
                'upload'      => [
                    'deleteTip' => __('press delete to remove', 'webmakerr-cart'),
                    'delete'    => __('Delete', 'webmakerr-cart'),
                    'preview'   => __('Preview', 'webmakerr-cart'),
                    'continue'  => __('Continue', 'webmakerr-cart'),
                ],
                'slider'      => [
                    'defaultLabel'           => __('slider between {min} and {max}', 'webmakerr-cart'),
                    'defaultRangeStartLabel' => __('pick start value', 'webmakerr-cart'),
                    'defaultRangeEndLabel'   => __('pick end value', 'webmakerr-cart'),
                ],
                'table'       => [
                    'emptyText'     => __('No Data', 'webmakerr-cart'),
                    'confirmFilter' => __('Confirm', 'webmakerr-cart'),
                    'resetFilter'   => __('Reset', 'webmakerr-cart'),
                    'clearFilter'   => __('All', 'webmakerr-cart'),
                    'sumText'       => __('Sum', 'webmakerr-cart'),
                ],
                'tour'        => [
                    'next'     => __('Next', 'webmakerr-cart'),
                    'previous' => __('Previous', 'webmakerr-cart'),
                    'finish'   => __('Finish', 'webmakerr-cart'),
                    'close'    => __('Close this dialog', 'webmakerr-cart'),
                ],
                'tree'        => [
                    'emptyText' => __('No Data', 'webmakerr-cart'),
                ],
                'transfer'    => [
                    'noMatch'           => __('No matching data', 'webmakerr-cart'),
                    'noData'            => __('No data', 'webmakerr-cart'),
                    'titles'            => [__('List 1', 'webmakerr-cart'), __('List 2', 'webmakerr-cart')],
                    'filterPlaceholder' => __('Enter keyword', 'webmakerr-cart'),
                    'noCheckedFormat'   => __('{total} items', 'webmakerr-cart'),
                    'hasCheckedFormat'  => __('{checked}/{total} checked', 'webmakerr-cart'),
                ],
                'image'       => [
                    'error' => __('FAILED', 'webmakerr-cart'),
                ],
                'pageHeader'  => [
                    'title' => __('Back', 'webmakerr-cart'),
                ],
                'popconfirm'  => [
                    'confirmButtonText' => __('Yes', 'webmakerr-cart'),
                    'cancelButtonText'  => __('No', 'webmakerr-cart'),
                ],
                'carousel'    => [
                    'leftArrow'  => __('Carousel arrow left', 'webmakerr-cart'),
                    'rightArrow' => __('Carousel arrow right', 'webmakerr-cart'),
                    'indicator'  => __('Carousel switch to index {index}', 'webmakerr-cart'),
                ],
            ],
        ];
    }

    public static function dateTimeStrings(): array
    {
        return [
            'weekdays'      => array(
                'sunday'    => _x('Sunday', 'weekdays', 'webmakerr-cart'),
                'monday'    => _x('Monday', 'weekdays', 'webmakerr-cart'),
                'tuesday'   => _x('Tuesday', 'weekdays', 'webmakerr-cart'),
                'wednesday' => _x('Wednesday', 'weekdays', 'webmakerr-cart'),
                'thursday'  => _x('Thursday', 'weekdays', 'webmakerr-cart'),
                'friday'    => _x('Friday', 'weekdays', 'webmakerr-cart'),
                'saturday'  => _x('Saturday', 'weekdays', 'webmakerr-cart'),
            ),
            'months'        => array(
                'January'   => _x('January', 'months', 'webmakerr-cart'),
                'February'  => _x('February', 'months', 'webmakerr-cart'),
                'March'     => _x('March', 'months', 'webmakerr-cart'),
                'April'     => _x('April', 'months', 'webmakerr-cart'),
                'May'       => _x('May', 'months', 'webmakerr-cart'),
                'June'      => _x('June', 'months', 'webmakerr-cart'),
                'July'      => _x('July', 'months', 'webmakerr-cart'),
                'August'    => _x('August', 'months', 'webmakerr-cart'),
                'September' => _x('September', 'months', 'webmakerr-cart'),
                'October'   => _x('October', 'months', 'webmakerr-cart'),
                'November'  => _x('November', 'months', 'webmakerr-cart'),
                'December'  => _x('December', 'months', 'webmakerr-cart')
            ),
            'weekdaysShort' => array(
                'sun' => _x('Sun', 'weekdaysShort', 'webmakerr-cart'),
                'mon' => _x('Mon', 'weekdaysShort', 'webmakerr-cart'),
                'tue' => _x('Tue', 'weekdaysShort', 'webmakerr-cart'),
                'wed' => _x('Wed', 'weekdaysShort', 'webmakerr-cart'),
                'thu' => _x('Thu', 'weekdaysShort', 'webmakerr-cart'),
                'fri' => _x('Fri', 'weekdaysShort', 'webmakerr-cart'),
                'sat' => _x('Sat', 'weekdaysShort', 'webmakerr-cart')
            ),
            'monthsShort'   => array(
                'jan' => _x('Jan', 'monthsShort', 'webmakerr-cart'),
                'feb' => _x('Feb', 'monthsShort', 'webmakerr-cart'),
                'mar' => _x('Mar', 'monthsShort', 'webmakerr-cart'),
                'apr' => _x('Apr', 'monthsShort', 'webmakerr-cart'),
                'may' => _x('May', 'monthsShort', 'webmakerr-cart'),
                'jun' => _x('Jun', 'monthsShort', 'webmakerr-cart'),
                'jul' => _x('Jul', 'monthsShort', 'webmakerr-cart'),
                'aug' => _x('Aug', 'monthsShort', 'webmakerr-cart'),
                'sep' => _x('Sep', 'monthsShort', 'webmakerr-cart'),
                'oct' => _x('Oct', 'monthsShort', 'webmakerr-cart'),
                'nov' => _x('Nov', 'monthsShort', 'webmakerr-cart'),
                'dec' => _x('Dec', 'monthsShort', 'webmakerr-cart')
            ),
            'am'            => __('AM', 'webmakerr-cart'),
            'pm'            => __('PM', 'webmakerr-cart'),
            'numericSystem' => _x('0_1_2_3_4_5_6_7_8_9', 'numeric system - Sequence must need to maintained', 'webmakerr-cart'),
        ];
    }
}