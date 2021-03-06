<?php

namespace Aventura\Edd\Bookings\Renderer;

use \Aventura\Diary\Bookable\Availability\Timetable\Rule\RangeRuleAbstract;
use \Aventura\Diary\DateTime;
use \Aventura\Diary\DateTime\Duration;
use \Aventura\Edd\Bookings\Availability\Rule\Renderer\RuleRendererInterface;
use \Aventura\Edd\Bookings\Model\Availability;
use \Exception;
use \InvalidArgumentException;

/**
 * An object that can render an availability.
 *
 * @author Miguel Muscat <miguelmuscat93@gmail.com>
 */
class AvailabilityRenderer extends RendererAbstract
{

    // Namespace shortcut constants
    const DIARY_RULE_NS = 'Aventura\\Diary\\Bookable\\Availability\\Timetable\\Rule\\';
    const EDD_BK_RULE_NS = 'Aventura\\Edd\\Bookings\\Availability\\Rule\\';
    const RENDERER_NS = 'Aventura\\Edd\\Bookings\\Availability\\Rule\\Renderer\\';

    /**
     * Constructs a new instance.
     * 
     * @param Availability $availability The availability to render.
     */
    public function __construct(Availability $availability)
    {
        parent::__construct($availability);
    }

    /**
     * {@inheritdoc}
     * 
     * @return Availability
     */
    public function getObject()
    {
        return parent::getObject();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $args = array())
    {
        $availability = $this->getObject();
        $defaults = array(
            'doc_link'      => true,
            'timezone_help' => true
        );
        $data = wp_parse_args($args, $defaults);
        ob_start();
        ?>
        <div class="edd-bk-availability-container" data-id="<?php echo $availability->getId(); ?>">
            <?php
            // Use nonce for verification
            \wp_nonce_field('edd_bk_save_meta', 'edd_bk_availability');
            // Use nonce for ajax
            \wp_nonce_field('edd_bk_availability_ajax', 'edd_bk_availability_ajax_nonce');
            ?>
            <div class="edd-bk-builder">
                <?php // Heading ?>
                <div class="edd-bk-row edd-bk-header">
                    <?php
                    foreach(static::getRulesTableColumns() as $col => $text) {
                        printf('<div class="edd-bk-heading edd-bk-col-%s">%s</div>', $col, $text);
                    }
                    ?>
                </div>
                <?php // Body ?>
                <div class="edd-bk-body">
                    <div class="edd-bk-row edd-bk-if-no-rules">
                        <div class="edd-bk-col-no-rules">
                            <span>
                                <i class="fa fa-info-circle"></i>
                                <?php _e('You have no availability times set up! Click the "Add" button below to get started.', 'eddbk'); ?>
                            </span>
                        </div>
                    </div>
                    <?php
                    foreach ($availability->getRules() as $rule) {
                        echo static::renderRule($rule);
                    }
                    ?>
                </div>
                <?php // Footer ?>
                <div class="edd-bk-row edd-bk-footer">
                    <div class="edd-bk-footing edd-bk-col-help">
                        <span>
                            <?php _e('Rules further down the table take priority.', 'eddbk'); ?>
                        </span>
                        <?php if ($data['doc_link']) : ?>
                            <span>
                                <a href="<?php echo EDD_BK_DOCS_URL ?>" target="_blank">
                                    <?php _e('Check out our documentation for help.', 'eddbk'); ?>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div><?php
                    // Can't leave spaces
                    ?><div class="edd-bk-footing edd-bk-col-add-rule">
                        <button class="button button-secondary" type="button">
                            <i class="edd-bk-add-rule-icon fa fa-plus fa-fw"></i>
                            <i class="edd-bk-add-rule-loading fa fa-hourglass-half fa-fw"></i>
                            <?php _e('Add', 'eddbk'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php if ($data['timezone_help']) : ?>
                <p>
                    <?php
                    // Indicate usage of WP timezone
                    _e("Dates and times entered above are treated as relative to your WordPress site's timezone.", 'eddbk');
                    echo ' ';
                    // Link to WP timezone setting
                    $link = sprintf('href="%s" target="_blank"', admin_url('options-general.php'));
                    printf(
                        _x('You can change this timezone from the %s.', '%s = link to general settings page', 'eddbk'),
                        sprintf('<a %1$s>%2$s</a>', $link, __('General Settings page', 'eddbk'))
                    );
                    echo ' ';
                    // Current date and time
                    _e('Your current date and time is: ', 'eddbk');
                    ?>
                    <code>
                        <?php
                        $gmtOffset = intval(get_option('gmt_offset'));
                        $gmt = ($gmtOffset<0? '' : '+') . $gmtOffset;
                        $datetime = DateTime::now();
                        $datetime->plus(Duration::hours($gmtOffset));
                        $format = sprintf('%s %s', get_option('date_format'), get_option('time_format'));
                        printf('%s GMT%s', $datetime->format($format), $gmt);
                        ?>
                    </code>
                </p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders the availability calendar preview
     */
    public function renderPreview()
    {
        $availability = $this->getObject();
        $id = $availability->getId();
        ob_start();
        ?>
        <div class="edd-bk-calendar-preview">
            <label>
                <?php _e('Preview using:', 'eddbk'); ?>
                <select class="edd-bk-calendar-preview-service">
                    <?php
                    $schedules = eddBookings()->getScheduleController()->getSchedulesForAvailability($id);
                    $scheduleIds = array_map(function($item) {
                        return $item->getId();
                    }, $schedules);
                    $services = eddBookings()->getServiceController()->getServicesForSchedule($scheduleIds);
                    foreach ($services as $service) {
                        $serviceId = $service->getId();
                        $serviceName = \get_the_title($serviceId);
                        printf('<option value="%s">%s</option>', $serviceId, $serviceName);
                    }
                    ?>
                </select>
            </label>
            <hr/>
            <div class="edd-bk-datepicker-container">
                <div class="edd-bk-datepicker-skin">
                    <div class="edd-bk-datepicker"></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Gets the table columns.
     * 
     * @return array An assoc array with column IDs as array keys and column labels as array values.
     */
    public static function getRulesTableColumns()
    {
        $columns = array(
                'move'      => '',
                'time-unit' => __('Time Unit', 'eddbk'),
                'start'     => __('Start', 'eddbk'),
                'end'       => __('End', 'eddbk'),
                'available' => __('Available', 'eddbk'),
                'remove'    => '',
        );
        $filteredColumns = \apply_filters('edd_bk_availability_rules_table_columns', $columns);
        return $filteredColumns;
    }

    /**
     * Renders the selector HTML element for the rule types.
     * 
     * @param type $selectedRule
     * @return type
     */
    public static function renderRangeTypeSelector($selectedRule = null)
    {
        $optionGroups = '';
        foreach (static::getRuleTypesGrouped() as $group => $rules) {
            $optionGroups .= sprintf('<optgroup label="%s">', $group);
            $options = '';
            foreach ($rules as $ruleClass => $ruleRendererClass) {
                $ruleName = $ruleRendererClass::getDefault()->getRuleName();
                $selected = \selected($ruleClass, $selectedRule, false);
                $options .= sprintf('<option value="%s" %s>%s</option>', $ruleClass, $selected, $ruleName);
            }
            $optionGroups .= sprintf('%s</optgroup>', $options);
        }
        return sprintf('<select>%s</select>', $optionGroups);
    }

    /**
     * Render the row for a specific rule instance.
     * 
     * @param RangeRuleAbstract|string|null $rule The rule.
     * @return string The rendered HTML.
     */
    public static function renderRule($rule)
    {
        if (is_null($rule)) {
            $ruleClass = key(static::getRuleTypes());
            $rendererClass = current(static::getRuleTypes());
            $renderer = $rendererClass::getDefault();
        } elseif (is_string($rule)) {
            $ruleClass = $rule;
            $rendererClass = static::getRuleRendererClassName($ruleClass);
            $renderer = $rendererClass::getDefault();
        } elseif (is_a($rule, '\\Aventura\\Diary\\Bookable\\Availability\\Timetable\\Rule\\RangeRuleAbstract')) {
            // Get the rule renderer
            $ruleClass = get_class($rule);
            $rendererClass = static::getRuleRendererClassName($ruleClass);
            $renderer = new $rendererClass($rule);
        } else {
            throw new InvalidArgumentException('Argument is not a string, RangeRuleAbstract instance or null.');
        }
        // Generate the rule type selector output
        $ruleSelector = static::renderRangeTypeSelector($ruleClass);
        // Generate output
        $output = '';
        $layout = '<div class="edd-bk-col-%s">%s</div>';
        $output .= sprintf($layout, 'move', static::renderMoveHandle());
        $output .= sprintf($layout, 'time-unit', $ruleSelector);
        $output .= sprintf($layout, 'start', $renderer->renderRangeStart());
        $output .= sprintf($layout, 'end', $renderer->renderRangeEnd());
        $output .= sprintf($layout, 'available', $renderer->renderAvailable());
        $output .= sprintf($layout, 'remove', static::renderRemoveHandle());
        return sprintf('<div class="edd-bk-row">%s</div>', $output);
    }

    /**
     * Renders the row move handle.
     * 
     * @return string
     */
    public static function renderMoveHandle()
    {
        return '<i class="fa fa-arrows-v edd-bk-move-handle"></i>';
    }

    /**
     * Renders the row remove handle.
     * 
     * @return string
     */
    public static function renderRemoveHandle()
    {
        return '<i class="fa fa-times edd-bk-remove-handle"></i>';
    }

    /**
     * Gets the rule types.
     * 
     * @return array An associative array with rule ID as array keys and their respective renderer class names as
     *               array keys.
     */
    public static function getRuleTypes()
    {
        $ruleTypes = array(
                static::EDD_BK_RULE_NS . 'DotwRule'          => static::RENDERER_NS . 'DotwRangeRenderer',
                static::EDD_BK_RULE_NS . 'WeekNumRule'       => static::RENDERER_NS . 'WeekNumRangeRenderer',
                static::EDD_BK_RULE_NS . 'MonthRule'         => static::RENDERER_NS . 'MonthRangeRenderer',
                static::EDD_BK_RULE_NS . 'CustomDateRule'    => static::RENDERER_NS . 'DateTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'MondayTimeRule'    => static::RENDERER_NS . 'MondayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'TuesdayTimeRule'   => static::RENDERER_NS . 'TuesdayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'WednesdayTimeRule' => static::RENDERER_NS . 'WednesdayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'ThursdayTimeRule'  => static::RENDERER_NS . 'ThursdayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'FridayTimeRule'    => static::RENDERER_NS . 'FridayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'SaturdayTimeRule'  => static::RENDERER_NS . 'SaturdayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'SundayTimeRule'    => static::RENDERER_NS . 'SundayTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'AllWeekTimeRule'   => static::RENDERER_NS . 'AllWeekTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'WeekdaysTimeRule'  => static::RENDERER_NS . 'WeekdaysTimeRangeRenderer',
                static::EDD_BK_RULE_NS . 'WeekendTimeRule'   => static::RENDERER_NS . 'WeekendTimeRangeRenderer',
        );
        $filtered = \apply_filters('edd_bk_availability_rule_types', $ruleTypes);
        return $filtered;
    }

    /**
     * Gets the rules, grouped according to their renderer's group.
     * 
     * @return array An associative array with group names as array keys and associative subarrays as array values.
     *               Each subarray has rule class name as array keys and their respective renderer class name as
     *               array values.
     */
    public static function getRuleTypesGrouped()
    {
        $ruleTypes = static::getRuleTypes();
        $grouped = array();
        foreach ($ruleTypes as $ruleClass => $rendererClass) {
            /* @var $defaultRenderer RuleRendererInterface */
            $defaultRenderer = $rendererClass::getDefault();
            $group = $defaultRenderer->getRuleGroup();
            // Create group if not in $grouped array
            if (!isset($grouped[$group])) {
                $grouped[$group] = array();
            }
            // Add to the $grouped array
            $grouped[$group][$ruleClass] = $rendererClass;
        }
        $filtered = \apply_filters('edd_bk_availability_rule_types_grouped', $grouped);
        return $filtered;
    }

    /**
     * Gets the renderer class name for a specific rule.
     * 
     * @param string $ruleClass The rule class.
     * @return string The renderer class name.
     * @throws Exception If the rule class given does not exist.
     */
    public static function getRuleRendererClassName($ruleClass)
    {
        $ruleTypes = static::getRuleTypes();
        $sanitizedRuleClass = str_replace('\\\\', '\\', $ruleClass);
        if (!isset($ruleTypes[$sanitizedRuleClass])) {
            throw new Exception(sprintf('The rule type class "%s" does not exist!', $sanitizedRuleClass));
        }
        $rendererClass = $ruleTypes[$sanitizedRuleClass];
        return $rendererClass;
    }

}
