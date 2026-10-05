import { announcement } from '../../data/content.js';
import './AnnouncementBar.css';

export default function AnnouncementBar() {
  return (
    <div className="announcement">
      <p className="announcement__text only-desktop">{announcement.desktop}</p>
      <p className="announcement__text only-mobile">{announcement.mobile}</p>
    </div>
  );
}
