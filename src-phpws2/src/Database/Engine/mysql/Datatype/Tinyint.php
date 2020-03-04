<?php

namespace phpws2\Database\Engine\mysql\Datatype;

/*
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Tinyint extends \phpws2\Database\Datatype\Integer
{

    protected $signed_limit_low = -128;
    protected $signed_limit_high = 127;
    protected $unsigned_limit_high = 255;

}
