module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            autoindex: {
                command: 'php vendor/pagamastarde/autoindex/index.php .'
            },
            composerProd: {
                command: 'composer install --no-dev'
            },
            composerDev: {
                command: 'composer install'
            },
            runTestPrestashop17: {
                command:
                    'docker-compose up -d prestashop17\n' +
                    'echo "Creating the $1 shop this will take 2 minutes"\n' +
                    'sleep 120\n' +
                    'docker logs prestashop_prestashop17_1\n' +
                    'echo "adjust the time in order to see the apache start logs"\n' +
                    'composer install && vendor/bin/phpunit --group prestashop17 --group basic\n' +
                    'composer install && vendor/bin/phpunit --group prestashop17 --group install\n' +
                    'composer install && vendor/bin/phpunit --group prestashop17 --group register\n' +
                    'composer install && vendor/bin/phpunit --group prestashop17 --group buy\n'
            },
            startTestScenario: {
                command:
                'docker-compose down\n' +
                'docker-compose up -d selenium\n'
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
                    {src: 'config.xml', dest: 'paylater/'},
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
        'shell:autoindex',
        'shell:composerProd',
        'compress',
        'shell:startTestScenario',
        'shell:runTestPrestashop17',
        'cssmin',
        'shell:autoindex',
        'shell:composerProd',
        'compress',
        'shell:composerDev'
    ]);
};
