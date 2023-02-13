module.exports = {
  presets: ['module:metro-react-native-babel-preset'],

  plugins: [
    [
      'module-resolver',
      {
        alias: {
          '@app/assets': './src/assets',
          '@app/components': './src/components',
          '@app/constants': './src/constants',
          '@app/screens': './src/screens',
          '@app/navigations': './src/navigations',
          '@app/store': './src/store',
          '@app/utils': './src/utils',
          '@app/config': './src/config',
          '@app/services': './src/services',
        },
      },
    ],
    // 'react-native-reanimated/plugin',
  ],
};
