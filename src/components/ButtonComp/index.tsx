import {StyleSheet, Text, View} from 'react-native';
import React from 'react';

type Props = {
  title: String;
};

const ButtonComp = (props:Props) => {
  return (
    <View style={styles.button}>
      <Text>Button {props.title}</Text>
    </View>
  );
};

export default ButtonComp;

const styles = StyleSheet.create({
  button: {
    height: 40,
    width: 300,
    backgroundColor: 'green',
  },
});
