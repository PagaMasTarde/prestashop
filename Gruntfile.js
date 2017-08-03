module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            autoindex: {
                command: 'php vendor/pagamastarde/autoindex/index.php .'
            },
            composerProd: {
                command: 'rm -rf vendor && composer install --no-dev'
            },
            composerDev: {
                command: 'composer install'
            },
            runTestPrestashop17: {
                command:
                'docker-compose --version\n' +
                'docker-compose down\n' +
                'docker-compose up -d selenium\n' +
                'docker-compose up -d prestashop17\n' +
                'echo "Creating the prestashop17"\n' +
                'sleep 180\n' +
                'docker-compose logs prestashop17\n' +
                'composer install && vendor/bin/phpunit --group prestashop17basic\n' +
                'composer install && vendor/bin/phpunit --group prestashop17install\n' +
                'composer install && vendor/bin/phpunit --group prestashop17register\n' +
                'composer install && vendor/bin/phpunit --group prestashop17buy\n'
            },
            runTestPrestashop16: {
                command:
                'docker-compose --version\n' +
                'docker-compose down\n' +
                'docker-compose up -d selenium\n' +
                'docker-compose up -d prestashop16\n' +
                'echo "Creating the prestashop16"\n' +
                'sleep 180\n' +
                'docker-compose logs prestashop16\n' +
                'composer install && vendor/bin/phpunit --group prestashop16basic\n' +
                'composer install && vendor/bin/phpunit --group prestashop16install\n' +
                'composer install && vendor/bin/phpunit --group prestashop16register\n' +
                'composer install && vendor/bin/phpunit --group prestashop16buy\n'
            },
            runTestPrestashop15: {
                command:
                'docker-compose --version\n' +
                'docker-compose down\n' +
                'docker-compose up -d selenium\n' +
                'docker-compose up -d prestashop15\n' +
                'echo "Creating the prestashop15"\n' +
                'sleep 180\n' +
                'docker-compose logs prestashop15\n' +
                'composer install && vendor/bin/phpunit --group prestashop15basic\n' +
                'composer install && vendor/bin/phpunit --group prestashop15install\n' +
                'composer install && vendor/bin/phpunit --group prestashop15register\n' +
                'composer install && vendor/bin/phpunit --group prestashop15buy\n'
            }
        },
        cssmin: {
            target: {
                files: [{
                    expand: true,
                    cwd: 'src/css',
                    src: ['*.css', '!*.min.css'],
                    dest: 'views/css',
                    ext: '.css'
                }]
            }
        },
        compress: {
            main: {
                options: {
                    archive: 'paylater.zip'
                },
                files: [
                    {src: ['controllers/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['classes/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['docs/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['override/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['logs/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['vendor/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['translations/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['upgrade/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['optionaloverride/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['oldoverride/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['sql/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['lib/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['defaultoverride/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['views/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: 'index.php', dest: 'paylater/'},
                    {src: 'paylater.php', dest: 'paylater/'},
                    {src: 'logo.png', dest: 'paylater/'},
                    {src: 'logo.gif', dest: 'paylater/'},
                    {src: 'LICENSE.md', dest: 'paylater/'},
                    {src: 'CONTRIBUTORS.md', dest: 'paylater/'},
                    {src: 'README.md', dest: 'paylater/'}
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.registerTask('default', [
        'shell:composerDev',
        'cssmin',
        'shell:composerProd',
        'shell:autoindex',
        'compress',
        'shell:composerDev',
    ]);

    //manually run the selenium test: "grunt shell:testPrestashop16"
};
