<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\EventOccurrence;
use App\Entity\News;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\ProjectMember;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use mysqli;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MigrationController extends AbstractController
{
    private $messages = array();

    function l($text) {
        $this->messages[] = $text;
    }

    function getDb() {
        $db_host = 'localhost';
        $db_user = 'assoesaip_prod';
        $db_pass = '&~uZKb$#{c_E&5H*j"A5';
        $db = 'assoesaip_prod';

        $mysqli = new mysqli($db_host, $db_user, $db_pass, $db) or die($mysqli->error);
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
    }

    /**
     * @Route("/migrate/users", name="migration_users")
     */
    public function index(): Response
    {
        $mysqli = $this->getDb();

        $this->l('starting migration...');
        $this->l('migrating users...');

        $em = $this->getDoctrine()->getManager();

        $query = "SELECT * FROM users";
        $result = $mysqli->query($query);
        while ($r = $result->fetch_assoc()) {
            $user = new User();
            $user->setFirstName($r['name']);
            $user->setLastName($r['surname']);
            $user->setUsername($r['email']);
            $user->setMsId($r['ms_id']);
            $user->setFirstLogin($r['premiere_co']);
            $user->setLastLogin($r['derniere_co']);
            if ($r['admin']) {
                $user->setRoles(['ROLE_ADMIN']);
            }
            $em->persist($user);
        }
        $em->flush();

        $this->l('users migrated.');

        //TODO Copier les photos de profil

        return $this->render('migration/index.html.twig', [
            'messages' => $this->messages,
        ]);
    }

    /**
     * @Route("/migrate/projects", name="migration_projects")
     * @IsGranted("ROLE_ADMIN")
     */
    public function projects(SluggerInterface $slugger): Response
    {
        $mysqli = $this->getDb();

        $this->l('starting migration...');
        $this->l('migrating projects...');

        $em = $this->getDoctrine()->getManager();

        $ncat = $this->getDoctrine()->getRepository(ProjectCategory::class)->find(6);
        $acat = $this->getDoctrine()->getRepository(ArticleCategory::class)->find(1);
        $ecat = $this->getDoctrine()->getRepository(EventCategory::class)->find(1);

        $query = "SELECT * FROM projets";
        $result = $mysqli->query($query);

        $metadata = $em->getClassMetaData(Project::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        $userRepo = $this->getDoctrine()->getRepository(User::class);

        while ($r = $result->fetch_assoc()) {
            $this->l('migrating ' . $r['nom']);
            $p = new Project();
            $p->setId($r['id']);
            $p->setType($r['type']);
            $p->setCategory($ncat);
            $p->setName($r['nom']);
            $p->setCampus($r['campus']);
            $p->setUrl($r['url']);
            $p->setDescription(substr($r['desc_courte'], 0, 180));
            $p->setKeywords($r['mots_cles']);
            $p->setHtml($r['corps_page']);
            $p->setEmail($r['mail']);
            $p->setDateAdded($r['dt_creation']);
            $p->setDateModified($r['dt_modification']);
            $em->persist($p);
            $em->flush();

            $this->l('migrating members');
            $query = "SELECT * FROM membres_projets WHERE projet_id = " . $r['id'];
            $res = $mysqli->query($query);
            $i = 0;
            while ($s = $res->fetch_assoc()) {
                $pm = new ProjectMember();
                $pm->setProject($p);
                $pm->setAdmin($s['redacteur']);
                $pm->setOrderPosition($i);
                $pm->setIntroduction('');

                $u = $userRepo->findOneBy(['msId' => $s['ms_id']]);
                $pm->setUser($u);
                $pm->setRole($s['role']);
                $em->persist($pm);
                $i++;
            }

            $this->l('migrating articles');
            $query = "SELECT * FROM articles WHERE id_projet = " . $r['id'];
            $res = $mysqli->query($query);

            while ($s = $res->fetch_assoc()) {
                $a = new Article();
                $a->setTitle(substr($s['titre'], 0, 60));
                $a->setAbstract(substr($s['contenu_court'], 0, 180));
                $a->setHtml($s['html']);
                $a->setDateCreated(new DateTime($s['date_publication']));
                $a->setDatePublished(new DateTime($s['date_publication']));
                $a->setPublished(true);
                $a->setPrivate(false);
                $a->setProject($p);
                $a->setDateEdited(new DateTime($s['date_modif']));
                $a->setCategory($acat);
                $slug = $slugger->slug($s['titre']);
                $a->setUrl($slug);

                $u = $userRepo->findOneBy(['msId' => $s['id_auteur']]);
                $a->setAuthor($u);

                $em->persist($a);
                $em->flush();

                $n = new News();
                $n->setProject($p);
                $n->setDatePublished(new DateTime($s['date_publication']));
                $n->setArticle($a);
                $em->persist($n);
            }

            $this->l('migrating events');
            $query = "SELECT * FROM evenements WHERE id_projet = " . $r['id'];
            $res = $mysqli->query($query);

            while ($s = $res->fetch_assoc()) {
                $e = new Event();
                $e->setTitle(substr($s['titre'], 0, 60));
                $e->setAbstract(substr($s['contenu_court'], 0, 180));
                $e->setHtml($s['html']);
                $e->setDateCreated(new DateTime($s['date_publication']));
                $e->setDatePublished(new DateTime($s['date_publication']));
                $e->setPublished(true);
                $e->setPrivate(false);
                $e->setProject($p);
                $e->setDateEdited(new DateTime($s['date_modif']));
                $e->setCategory($ecat);
                $slug = $slugger->slug($s['titre']);
                $e->setUrl($slug);

                $e->setDateStart(new DateTime($s['date_debut']));
                $e->setDateEnd(new DateTime($s['date_fin']));
                $e->setDuration($s['duree']);
                $e->setAllDay($s['all_day']);
                $e->setDaysOfWeek(explode(',', $s['days_of_week']));
                $e->setIntervalCount($s['interval_count']);
                $e->setIntervalType($s['interval_type']);
                $e->setOccurrencesCount($s['occurrences']);

                $u = $userRepo->findOneBy(['msId' => $s['id_auteur']]);
                $e->setAuthor($u);

                $em->persist($e);
                $em->flush();

                $n = new News();
                $n->setProject($p);
                $n->setDatePublished(new DateTime($s['date_publication']));
                $n->setEvent($e);
                $em->persist($n);

                $query = "SELECT * FROM occur_evenements WHERE id_event = " . $s['id'];
                $re = $mysqli->query($query);
                while ($t = $re->fetch_assoc()) {
                    $e->addOccurrence(new EventOccurrence(new DateTime($t['date'])));
                }
                $em->persist($e);
            }
            
            //TODO images articles + events
        }

        $em->flush();

        //TODO Reseaux sociaux + logo + images page projet

        $this->l('projects migrated.');

        return $this->render('migration/index.html.twig', [
            'messages' => $this->messages,
        ]);
    }
}
