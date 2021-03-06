<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\Snippet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CSnippetFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $language = new Language();
        $language->setName('C');
        $manager->persist($language);

        $snippet = new Snippet();
        $snippet->setLanguage($language);
        $snippet->setName('helloworld');
        $snippet->setCode(
            '#include <stdio.h>' . PHP_EOL .
            'int main() {' . PHP_EOL .
            '    printf("Hello, world!\n")' . PHP_EOL .
            '    return 0;' . PHP_EOL .
            '}'
        );
        $manager->persist($snippet);

        $manager->flush();
    }
}
