<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Show a user's friends (subscriptions)
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
 * @author    Dan Moore <dan@moore.cx>
 * @author    Evan Prodromou <evan@status.net>
 * @author    Zach Copley <zach@status.net>
 * @copyright 2009 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET')) {
    exit(1);
}

require_once INSTALLDIR . '/lib/apibareauth.php';

/**
 * Ouputs the authenticating user's friends (subscriptions), each with
 * current Twitter-style status inline.  They are ordered by the date
 * in which the user subscribed to them, 100 at a time.
 *
 * @category API
 * @package  StatusNet
 * @author   Dan Moore <dan@moore.cx>
 * @author   Evan Prodromou <evan@status.net>
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class ApiUserFriendsAction extends ApiSubscriptionsAction
{
    /**
     * Get the user's subscriptions (friends) as an array of profiles
     *
     * @return array Profiles
     */
    function getProfiles()
    {
        $offset = ($this->page - 1) * $this->count;
        $limit =  $this->count + 1;

        $subs = null;

        if (isset($this->tag)) {
            $subs = $this->user->getTaggedSubscriptions(
                $this->tag, $offset, $limit
            );
        } else {
            $subs = $this->user->getSubscriptions(
                $offset,
                $limit
            );
        }

        $profiles = array();

        if (!empty($subs)) {
            while ($subs->fetch()) {
                $profiles[] = clone($subs);
            }
        }

        return $profiles;
    }
}
