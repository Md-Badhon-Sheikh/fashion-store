import { useEffect, useState } from 'react';
import './Countdown.css';

const pad = (n) => String(n).padStart(2, '0');

function remaining(endsAt) {
  const ms = Math.max(0, new Date(endsAt).getTime() - Date.now());
  const total = Math.floor(ms / 1000);
  return {
    done: total === 0,
    hours: Math.floor(total / 3600),
    minutes: Math.floor((total % 3600) / 60),
    seconds: total % 60,
  };
}

/**
 * Live HH:MM:SS countdown in dark boxes.
 *   <Countdown endsAt={flashSale.endsAt} />
 * Props: endsAt (Date | ms timestamp | ISO string), label ("Ends in"),
 *        size ("md" | "sm"), onEnd (called once at 00:00:00), className.
 * Screen readers get a minute-precision label instead of a ticking value.
 */
export default function Countdown({ endsAt, label = 'Ends in', size = 'md', onEnd, className = '' }) {
  const [time, setTime] = useState(() => remaining(endsAt));

  useEffect(() => {
    const tick = () => {
      const next = remaining(endsAt);
      setTime(next);
      return next.done;
    };
    if (tick()) {
      onEnd?.();
      return undefined;
    }
    const id = setInterval(() => {
      if (tick()) {
        clearInterval(id);
        onEnd?.();
      }
    }, 1000);
    return () => clearInterval(id);
  }, [endsAt, onEnd]);

  const srText = time.done
    ? 'Offer has ended'
    : `${label} ${time.hours} hours ${time.minutes} minutes`;

  return (
    <div className={`countdown countdown--${size} ${className}`.trim()} role="timer" aria-label={srText}>
      {label && <span aria-hidden="true">{label}</span>}
      <span className="countdown__box" aria-hidden="true">
        {pad(time.hours)}
      </span>
      <span aria-hidden="true">:</span>
      <span className="countdown__box" aria-hidden="true">
        {pad(time.minutes)}
      </span>
      <span aria-hidden="true">:</span>
      <span className="countdown__box" aria-hidden="true">
        {pad(time.seconds)}
      </span>
    </div>
  );
}
