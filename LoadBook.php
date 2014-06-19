<?php

namespace tr\rsywxBundle\DataFixtures\ORM;

use \Doctrine\Common\DataFixtures\AbstractFixture;
use \Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Doctrine\Common\Persistence\ObjectManager;
use \tr\rsywxBundle\Entity\BookBook as BookBook;

class LoadBookData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * 
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        //Create a special book
        $s = new BookBook();
        $s->setAuthor('Special');
        $s->setCategory('Special');
        $s->setCopyrighter('');
        $s->setDeco('S');
        $s->setInstock(1);
        $s->setTitle('Special Book Title');
        $s->setRegion('Somewhere On Earth');
        $s->setTranslated(0);
        $s->setPurchdate(new \DateTime('1970-1-1'));
        $s->setPubdate(new \DateTime('1970-1-1'));
        $s->setPrintdate(new \DateTime('1970-1-1'));
        $s->setVer('1.1');
        $s->setKword(999);
        $s->setPage(999);
        $s->setIsbn('123456789');
        $s->setPrice('9999.99');
        $s->setLocation('x1');
        $s->setIntro('This is a very special book.\nIt is special because it is purchased at EPOCH.');
        $s->setPublisher($this->getReference('specialPub'));
        $s->setPlace($this->getReference('specialPlace'));
        $s->setBookid('99999');
        $this->addReference('aBook', $s);
        
        $manager->persist($s);
        
        //Now we create a 100 general book
        for ($i = 1; $i <= 100; $i++)
        {
            $p = new BookBook();
            $p->setAuthor('Normal'.$i%5);
            $p->setCategory('Normal');
            $p->setCopyrighter('');
            $p->setDeco('N');
            $p->setInstock(1);
            $p->setTitle('Normal Book Title');
            $p->setRegion('Normal');
            $p->setTranslated(0);
            $p->setPurchdate(new \DateTime());
            $p->setPubdate(new \DateTime());
            $p->setPrintdate(new \DateTime());
            $p->setVer('1.1');
            $p->setKword($i);
            $p->setPage($i);
            $p->setIsbn("$i");
            $p->setPrice("$i.99");
            $p->setLocation('a1');
            $p->setIntro('This is a normal book.');
            $p->setPublisher($this->getReference('commonPub'));
            $p->setPlace($this->getReference('commonPlace'));
            $p->setBookid("$i");
            $manager->persist($p);
        }
        $manager->flush();

    }

    /**
     * 
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }

}
