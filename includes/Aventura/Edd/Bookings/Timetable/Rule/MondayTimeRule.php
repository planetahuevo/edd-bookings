<?php

namespace Aventura\Edd\Bookings\Timetable\Rule;

use \Aventura\Diary\DateTime\Day;

/**
 * MondayTimeRule
 *
 * @author Miguel Muscat <miguelmuscat93@gmail.com>
 */
class MondayTimeRule extends SingleDotwTimeRule
{

    const DOTW = Day::MONDAY;

}
