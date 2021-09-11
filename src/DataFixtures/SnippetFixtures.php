<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\Snippet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SnippetFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $language = new Language();
        $language->setName('C');
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
        $manager->persist($language);
        $manager->persist($snippet);

        $language = new Language();
        $language->setName('C++');
        $snippet = new Snippet();
        $snippet->setLanguage($language);
        $snippet->setName('helloworld');
        $snippet->setCode(
            '#include <iostream>' . PHP_EOL .
            'using namespace std;' . PHP_EOL .
            'int main() {' . PHP_EOL .
            '    cout << "Hello, world!" << endl;' . PHP_EOL .
            '    return 0;' . PHP_EOL .
            '}'
        );
        $manager->persist($language);
        $manager->persist($snippet);

        $manager->flush();
    }
}
