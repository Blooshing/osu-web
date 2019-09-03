/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

import Main from 'news-show/main';
import core from 'osu-core-singleton';

reactTurbolinks.registerPersistent('news-show', Main, true, (container: HTMLElement) => {
  const data = osu.parseJson('json-show');

  core.dataStore.commentStore.updateWithJSON(data.comment_bundle.comments);
  core.dataStore.userStore.updateWithJSON(data.comment_bundle.users);
  // core.dataStore.commentableMetaStore.updateWithJSON(data.comment_bundle.commentable_meta);

  return {
    commentBundle: data.comment_bundle,
    container,
    post: data.post,
  };
});
