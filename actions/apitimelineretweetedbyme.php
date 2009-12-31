<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Show authenticating user's most recent repeats
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  API
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET')) {
    exit(1);
}

require_once INSTALLDIR . '/lib/apiauth.php';
require_once INSTALLDIR . '/lib/mediafile.php';

/**
 * Show authenticating user's most recent repeats
 *
 * @category API
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */

class ApiTimelineRetweetedByMeAction extends ApiAuthAction
{
    const DEFAULTCOUNT = 20;
    const MAXCOUNT     = 200;
    const MAXNOTICES   = 3200;

    var $repeats  = null;
    var $cnt      = self::DEFAULTCOUNT;
    var $page     = 1;
    var $since_id = null;
    var $max_id   = null;

    /**
     * Take arguments for running
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     *
     */

    function prepare($args)
    {
        parent::prepare($args);

        $cnt = $this->int('count', self::DEFAULTCOUNT, self::MAXCOUNT, 1);

        $page = $this->int('page', 1, (self::MAXNOTICES/$this->cnt));

        $since_id = $this->int('since_id');

        $max_id = $this->int('max_id');

        return true;
    }

    /**
     * Handle the request
     *
     * show a timeline of the user's repeated notices
     *
     * @param array $args $_REQUEST data (unused)
     *
     * @return void
     */

    function handle($args)
    {
        parent::handle($args);

        $offset = ($this->page-1) * $this->cnt;
        $limit  = $this->cnt;

        $strm = $this->auth_user->repeatedByMe($offset, $limit, $this->since_id, $this->max_id);

        switch ($this->format) {
        case 'xml':
            $this->showXmlTimeline($strm);
            break;
        case 'json':
            $this->showJsonTimeline($strm);
            break;
        case 'atom':
            $profile    = $this->auth_user->getProfile();

            $title      = sprintf(_("Repeated by %s"), $this->auth_user->nickname);
            $taguribase = common_config('integration', 'taguri');
            $id         = "tag:$taguribase:RepeatedByMe:" . $this->auth_user->id;
            $link       = common_local_url('showstream',
                                           array('nickname' => $this->auth_user->nickname));

            $this->showAtomTimeline($strm, $title, $id, $link);
            break;

        default:
            $this->clientError(_('API method not found!'), $code = 404);
            break;
        }
    }
}