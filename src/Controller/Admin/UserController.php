<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Enum\DatumTypeEnum;
use App\Form\Type\Entity\Admin\UserType;
use App\Repository\AlbumRepository;
use App\Repository\CollectionRepository;
use App\Repository\DatumRepository;
use App\Repository\ItemRepository;
use App\Repository\PhotoRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use App\Repository\WishRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted("ROLE_ADMIN")]
class UserController extends AbstractController
{
    #[Route(
        path: ['en' => '/admin/users', 'fr' => '/admin/utilisateurs'],
        name: 'app_admin_user_index', methods: ['GET']
    )]
    public function index(UserRepository $userRepository) : Response
    {
        return $this->render('App/Admin/User/index.html.twig', [
            'users' => $userRepository->findBy([], ['lastDateOfActivity' => 'DESC'])
        ]);
    }

    #[Route(
        path: ['en' => '/admin/users/add', 'fr' => '/admin/utilisateurs/ajouter'],
        name: 'app_admin_user_add', methods: ['GET', 'POST']
    )]
    public function add(Request $request, TranslatorInterface $translator, ManagerRegistry $managerRegistry) : Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->persist($user);
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.user_added', ['%user%' => '&nbsp;<strong>'.$user->getUsername().'</strong>&nbsp;']));

            return $this->redirectToRoute('app_admin_user_index', ['id' => $user->getId()]);
        }

        return $this->render('App/Admin/User/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route(
        path: ['en' => '/admin/users/{id}/edit', 'fr' => '/admin/utilisateurs/{id}/editer'],
        name: 'app_admin_user_edit', methods: ['GET', 'POST']
    )]
    public function edit(Request $request, User $user, TranslatorInterface $translator, CollectionRepository $collectionRepository,
                         ItemRepository $itemRepository, TagRepository $tagRepository, WishlistRepository $wishlistRepository,
                         WishRepository $wishRepository, AlbumRepository $albumRepository, PhotoRepository $photoRepository,
                         DatumRepository $datumRepository, ManagerRegistry $managerRegistry
    ) : Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.user_edited', ['%user%' => '&nbsp;<strong>'.$user->getUsername().'</strong>&nbsp;']));

            return $this->redirectToRoute('app_admin_user_index', ['id' => $user->getId()]);
        }

        return $this->render('App/Admin/User/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'counters' => [
                'collections' => $collectionRepository->count(['owner' => $user]),
                'items' => $itemRepository->count(['owner' => $user]),
                'tags' => $tagRepository->count(['owner' => $user]),
                'wishlists' => $wishlistRepository->count(['owner' => $user]),
                'wishes' => $wishRepository->count(['owner' => $user]),
                'albums' => $albumRepository->count(['owner' => $user]),
                'photos' => $photoRepository->count(['owner' => $user]),
                'signs' => $datumRepository->count(['owner' => $user, 'type' => DatumTypeEnum::TYPE_SIGN]),
            ],
        ]);
    }
    
    #[Route(
        path: ['en' => '/admin/users/{id}/delete', 'fr' => '/admin/utilisateurs/{id}/supprimer'],
        name: 'app_admin_user_delete', requirements: ['id' => '%uuid_regex%'], methods: ['POST']
    )]
    public function delete(Request $request, User $user, TranslatorInterface $translator, ManagerRegistry $managerRegistry) : Response
    {
        if ($user->isAdmin()) {
            return $this->render('App/Admin/User/delete.html.twig', [
                'user' => $user,
                'error' =>  $translator->trans('error.cannot_delete_admin_user')
            ]);
        }

        $form = $this->createDeleteForm('app_admin_user_delete', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->remove($user);
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.user_deleted', ['%user%' => '&nbsp;<strong>'.$user->getUsername().'</strong>&nbsp;']));
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
