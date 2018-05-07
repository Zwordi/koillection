<?php

namespace App\Controller\Admin;

use App\Entity\Collection;
use App\Entity\Connection;
use App\Entity\Item;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wish;
use App\Entity\Wishlist;
use App\Enum\PeriodEnum;
use App\Form\Type\Entity\Admin\UserType;
use App\Service\DiskUsageCalculator;
use App\Service\Graph\ChartBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class UserController
 *
 * @package App\Controller
 *
 * @Route("/admin/users")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("", name="app_admin_user_index")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function index() : Response
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('App/Admin/User/index.html.twig', [
            'results' => $em->getRepository(User::class)->findAllWithConnectionsDetails()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_admin_user_edit", requirements={"id"="%uuid_regex%"})
     * @Method({"GET", "POST"})
     * @param User $user
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function edit(Request $request, User $user, TranslatorInterface $translator) : Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.user_edited', ['%user%' => '&nbsp;<strong>'.$user->getUsername().'</strong>&nbsp;']));

            return $this->redirect($this->generateUrl('app_admin_user_index', [
                'id' => $user->getId(),
            ]));
        }

        return $this->render('App/Admin/User/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/recompute-disk-usage", name="app_admin_user_recompute_disk_usage", requirements={"id"="%uuid_regex%"})
     * @Method({"GET"})
     *
     * @param User $user
     * @param DiskUsageCalculator $diskUsageCalculator
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function recomputeDiskUsage(User $user, DiskUsageCalculator $diskUsageCalculator, TranslatorInterface $translator) : Response
    {
        $user->setDiskSpaceUsed($diskUsageCalculator->getSpaceUsedByUser($user));
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', $translator->trans('message.done'));

        return $this->redirectToRoute('app_admin_user_index', [
            'id' => $user->getId()
        ]);
    }
}