<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2008, 2009, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
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
 */

if (!defined('STATUSNET') && !defined('LACONICA')) { exit(1); }

require_once INSTALLDIR . '/lib/oauthstore.php';

class ApiStatusNetOAuthDataStore extends StatusNetOAuthDataStore
{

    function lookup_consumer($consumer_key)
    {
        $con = Consumer::staticGet('consumer_key', $consumer_key);

        if (!$con) {
            return null;
        }

        return new OAuthConsumer($con->consumer_key,
                                 $con->consumer_secret);
    }

    function new_access_token($token, $consumer)
    {
        common_debug('new_access_token("'.$token->key.'","'.$consumer->key.'")', __FILE__);
        $rt = new Token();
        $rt->consumer_key = $consumer->key;
        $rt->tok = $token->key;
        $rt->type = 0; // request
        if ($rt->find(true) && $rt->state == 1) { // authorized
            common_debug('request token found.', __FILE__);
            $at = new Token();
            $at->consumer_key = $consumer->key;
            $at->tok = common_good_rand(16);
            $at->secret = common_good_rand(16);
            $at->type = 1; // access
            $at->created = DB_DataObject_Cast::dateTime();
            if (!$at->insert()) {
                $e = $at->_lastError;
                common_debug('access token "'.$at->tok.'" not inserted: "'.$e->message.'"', __FILE__);
                return null;
            } else {
                common_debug('access token "'.$at->tok.'" inserted', __FILE__);
                // burn the old one
                $orig_rt = clone($rt);
                $rt->state = 2; // used
                if (!$rt->update($orig_rt)) {
                    return null;
                }
                common_debug('request token "'.$rt->tok.'" updated', __FILE__);
                // Update subscription
                // XXX: mixing levels here
                $sub = Subscription::staticGet('token', $rt->tok);
                if (!$sub) {
                    return null;
                }
                common_debug('subscription for request token found', __FILE__);
                $orig_sub = clone($sub);
                $sub->token = $at->tok;
                $sub->secret = $at->secret;
                if (!$sub->update($orig_sub)) {
                    return null;
                } else {
                    common_debug('subscription updated to use access token', __FILE__);
                    return new OAuthToken($at->tok, $at->secret);
                }
            }
        } else {
            return null;
        }
    }

}
