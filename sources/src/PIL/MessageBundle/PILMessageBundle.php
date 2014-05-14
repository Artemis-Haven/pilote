<?php

namespace PIL\MessageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PILMessageBundle extends Bundle
{
    public function getParent() {
        return 'FOSMessageBundle';
    }
}
