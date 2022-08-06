<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user")
 * @IsGranted("ROLE_ADMIN") 
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="admin_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['created' => 'DESC']);        
        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/new", name="admin_user_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setCreated(new \DateTime());
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'admin.user.created_successfully');

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->renderForm('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, LoggerInterface $customLogger): Response
    {        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $uow = $entityManager->getUnitOfWork();
        if ($form->isSubmitted() && $form->isValid()) {
            $originalData = $uow->getOriginalEntityData($user);
            $entityManager->flush();            
            $latestData = $uow->getOriginalEntityData($user);
            
            $diff = [];
            foreach ($originalData as $key => $value) {
                if ($key == 'id') { continue; }
                
                if (!isset($latestData[$key])) {
                    $latestData[$key] = '';
                }
                
                if ($value == $latestData[$key]) { continue; }
                
                $diff[$key] = [
                    'from' => $value,
                    'to' => $latestData[$key]
                ];                
            }

            if (count($diff) > 0 ) {
                $customLogger->info('User data updated - ' . $user->getId() . ':: '. json_encode($diff));                
            }

            $this->addFlash('success', 'admin.user.updated_successfully');

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->renderForm('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="admin_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, LoggerInterface $customLogger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            
            $oldDeleted = $user->getDeleted();            
            $user->setDeleted(new \DateTime());
            
            $entityManager->flush();

            $newDeleted = $user->getDeleted();
            
            $diff = ['deleted' => ['from ' => $oldDeleted, 'to' => $newDeleted]];            
            
            $customLogger->info('User deleted "softly" - ' . $user->getId() . ':: '. json_encode($diff));                
            
            $this->addFlash('success', 'admin.user.deleted_successfully');            
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
