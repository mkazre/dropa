import React, { useRef, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  useWindowDimensions,
  Pressable,
  NativeSyntheticEvent,
  NativeScrollEvent,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Svg, { Rect, Line, Circle, Path } from 'react-native-svg';
import { colors } from '../theme/tokens';

type Slide = {
  key: string;
  title: string;
  body: string;
  Art: React.FC<{ size: number }>;
};

const ReserveArt: React.FC<{ size: number }> = ({ size }) => (
  <Svg width={size} height={size} viewBox="0 0 200 200">
    <Rect x={30} y={30} width={140} height={140} rx={28} fill={colors.signal} />
    <Rect x={64} y={64} width={72} height={72} rx={12} fill="none" stroke="#241f00" strokeWidth={7} />
    <Line x1={64} y1={100} x2={136} y2={100} stroke="#241f00" strokeWidth={6} />
  </Svg>
);

const ShareArt: React.FC<{ size: number }> = ({ size }) => (
  <Svg width={size} height={size} viewBox="0 0 200 200">
    <Rect x={20} y={20} width={160} height={160} rx={30} fill={colors.ink} />
    <Path d="M60 130l35-70 45 40 -15 30z" fill={colors.signal} />
    <Circle cx={95} cy={60} r={10} fill={colors.signal} />
  </Svg>
);

const CollectArt: React.FC<{ size: number }> = ({ size }) => (
  <Svg width={size} height={size} viewBox="0 0 200 200">
    <Rect x={30} y={30} width={140} height={140} rx={28} fill={colors.okSoft} />
    <Path d="M55 100l30 30 60-60" stroke={colors.ok} strokeWidth={12} fill="none" strokeLinecap="round" strokeLinejoin="round" />
  </Svg>
);

const slides: Slide[] = [
  {
    key: 'reserve',
    title: 'Expecting a delivery? Reserve a locker.',
    body: "Tell Dropa what's coming and we'll hold the right-sized locker at your building — no more guessing where a parcel will end up.",
    Art: ReserveArt,
  },
  {
    key: 'share',
    title: 'Share a code with your courier.',
    body: 'Send the drop-off code by SMS, WhatsApp or link. They enter it at the locker — no account, no app, no hassle for them.',
    Art: ShareArt,
  },
  {
    key: 'collect',
    title: 'Collect with a PIN, QR, or your phone.',
    body: "The moment it's dropped off, you're notified. Open the locker with your PIN, a QR scan, or right from the app when you're standing there.",
    Art: CollectArt,
  },
];

export default function OnboardingScreen({ onDone }: { onDone: () => void }) {
  const { width } = useWindowDimensions();
  const [index, setIndex] = useState(0);
  const listRef = useRef<FlatList<Slide>>(null);

  const onScroll = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    setIndex(Math.round(e.nativeEvent.contentOffset.x / width));
  };

  const next = () => {
    if (index < slides.length - 1) {
      listRef.current?.scrollToIndex({ index: index + 1 });
    } else {
      onDone();
    }
  };

  return (
    <SafeAreaView style={styles.screen}>
      <Pressable onPress={onDone} style={styles.skip}>
        <Text style={styles.skipText}>Skip</Text>
      </Pressable>

      <FlatList
        ref={listRef}
        data={slides}
        keyExtractor={(s) => s.key}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={onScroll}
        scrollEventThrottle={16}
        renderItem={({ item }) => (
          <View style={[styles.slide, { width }]}>
            <View style={styles.art}>
              <item.Art size={180} />
            </View>
            <Text style={styles.title}>{item.title}</Text>
            <Text style={styles.body}>{item.body}</Text>
          </View>
        )}
      />

      <View style={styles.dots}>
        {slides.map((s, i) => (
          <View key={s.key} style={[styles.dotPip, i === index && styles.dotPipActive]} />
        ))}
      </View>

      <Pressable style={styles.cta} onPress={next}>
        <Text style={styles.ctaText}>{index === slides.length - 1 ? 'Get started' : 'Next'}</Text>
      </Pressable>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: colors.paper },
  skip: { alignSelf: 'flex-end', padding: 20 },
  skipText: { color: colors.muted, fontWeight: '700', fontSize: 13 },
  slide: { alignItems: 'center', paddingHorizontal: 32, paddingTop: 10 },
  art: { marginBottom: 36 },
  title: { fontSize: 24, fontWeight: '800', letterSpacing: -0.6, textAlign: 'center', color: colors.ink },
  body: { fontSize: 14.5, color: colors.muted, textAlign: 'center', marginTop: 14, lineHeight: 21, maxWidth: 300 },
  dots: { flexDirection: 'row', justifyContent: 'center', gap: 6, marginBottom: 18 },
  dotPip: { width: 7, height: 7, borderRadius: 4, backgroundColor: colors.line },
  dotPipActive: { backgroundColor: colors.signalDeep, width: 20 },
  cta: {
    marginHorizontal: 24, marginBottom: 24, backgroundColor: colors.ink,
    borderRadius: 15, paddingVertical: 16, alignItems: 'center',
  },
  ctaText: { color: '#fff', fontWeight: '700', fontSize: 16 },
});
