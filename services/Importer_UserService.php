<?php
namespace Craft;

class Importer_UserService extends BaseApplicationComponent
{
    /**
     * Get a user by their email
     *
     * @param string $email
     * @return UserModel
     */
    public function getByEmail($email)
    {
        $criteria = craft()->elements->getCriteria(ElementType::User);
        $criteria->email = $email;

        if ($criteria->count() == 0)
            return null;

        return $criteria->first();
    }

    /**
     * Create a new user
     *
     * @param string $email
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param array $attributes
     * @return UserModel
     */
    public function createUser($email, $username, $firstName, $lastName, $attributes)
    {
        $user = new UserModel();

        $user->email = $email;
        $user->username = $username;
        $user->firstName = $firstName;
        $user->lastName = $lastName;
        $user->getContent()->setAttributes($attributes);

        return $user;
    }

    /**
     * Save a product
     *
     * @parem UserModel $user
     */
    public function save(\Craft\UserModel $user)
    {
        craft()->users->saveUser($user);
    }
}
