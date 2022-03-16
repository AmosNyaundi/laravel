<?php

  $leo = date("Ymd");
  $lasthour = date("H", strtotime( '-1 hours' ) );
  `cat /var/log/popsms/recharge_$leo-$lasthour.log >> /var/log/popsms/archive/recharge_$leo.log`;
  `rm -fr /var/log/popsms/recharge_$leo-$lasthour.log`;

  `cat /var/log/popsms/C2B_$leo-$lasthour.log >> /var/log/popsms/archive/C2B_$leo.log`;
  `rm -fr /var/log/popsms/C2B_$leo-$lasthour.log`;

  `cat /var/log/popsms/error_recharge_$leo-$lasthour.log >> /var/log/popsms/archive/error_recharge_$leo.log`;
  `rm -fr /var/log/popsms/error_recharge_$leo-$lasthour.log`;

  `cat /var/log/popsms/stkpush_$leo-$lasthour.log >> /var/log/popsms/archive/stkpush_$leo.log`;
  `rm -fr /var/log/popsms/stkpush_$leo-$lasthour.log`;
