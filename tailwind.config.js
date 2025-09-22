import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*',
        './resources/views/filament/**/*',
        './vendor/filament/**/*.blade.php',
    ],
}
