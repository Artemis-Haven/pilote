<?php

namespace PIL\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PILUserBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}
