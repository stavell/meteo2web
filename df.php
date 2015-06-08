<?php
echo shell_exec("df /dev/vda1 | sed  '/\d{1,}(?=%\s)/i'");
