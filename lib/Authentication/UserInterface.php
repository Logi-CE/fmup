<?php

namespace FMUP\Authentication;

/**
 * Description of UserInterface
 *
 * @author sweffling
 */
interface UserInterface
{

    /**
     * Gets the user from the application scope
     * @param string $login
     * @param string $password
     * @return UserInterface $user
     */
    public function auth($login, $password);
}
