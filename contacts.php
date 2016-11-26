<?php

@file_put_contents("contacts.txt",
    date('[Y-m-d H:i]'). ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_POST['email'] . ': ' . $_POST['message'] . "\n", FILE_APPEND);