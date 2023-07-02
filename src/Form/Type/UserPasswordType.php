<?php
/**
 * User password type.
 */

namespace App\Form\Type;

use App\Entity\User;
// use App\Form\DataTransformer\UserDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserPasswordType.
 */
class UserPasswordType extends AbstractType
{
    //    /**
    //     * User data transformer.
    //     *
    //     * @var UserDataTransformer
    //     */
    //    private UserDataTransformer $tagsDataTransformer;
    //
    //    /**
    //     * Constructor.
    //     *
    //     * @param UserDataTransformer $tagsDataTransformer User data transformer
    //     */
    //    public function __construct(UserDataTransformer $tagsDataTransformer)
    //    {
    //        $this->tagsDataTransformer = $tagsDataTransformer;
    //    }
    /**
     * Authorization Checker.
     */
    private AuthorizationCheckerInterface $authorizationChecker;
    /**
     * Interface for a user password hasher.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker Authorization Checker
     * @param UserPasswordHasherInterface   $passwordHasher       Password Hash
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, UserPasswordHasherInterface $passwordHasher)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'password',
            PasswordType::class,
            [
                'label' => 'label.password',
                'required' => true,
                'attr' => ['max_length' => 64],
            ]
        );
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $event->getData();

            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());

            // Set the hashed password
            $user->setPassword($hashedPassword);

            $event->setData($user);
        });

        //        $builder->get('tags')->addModelTransformer(
        //            $this->tagsDataTransformer
        //        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
