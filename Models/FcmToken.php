<?php
namespace Modules\FCM\Models;

use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{

    protected $table = 'fcm_token';

    /* user_type=0 is buyer */
    /* user_type=1 is seller */
    /* user_type=2 is admin */
    /* user_type=3 is delivery_company */
    /* user_type=4 is delivery_man */

}
