<template>
    <div class="content-container">
        <div class="top-bar">
            <button class="back-icon" @click="goBack">
                <ion-icon name="arrow-back-outline"></ion-icon>
            </button>
            <div class="profile-top">
                <p class="title" v-if="profileuser">{{ profileuser.Name }}</p>
                <p class="tweet_count">{{ calculateModifiedPostType() }}</p>
            </div>
        </div>
        <div class="profile-info">
            <div class="banner" v-if="profileuser" >
                <img class="banner-image" v-if="profileuser.Banner" :src="'/storage/' + profileuser.Banner">
                <div v-if="!profileuser.Banner" class="banner-image"></div>
                <div class="profile-picture">
                    <img :src="'/storage/' + profileuser.ProfilePicture">
                </div>
            </div>
            <div class="edit-button-div" v-if="profileuser">
                <button class="edit-profile" @click="() => TogglePopup('EditTrigger')" v-if="profileuser.UserID === user.UserID">Edit profile</button>
                <button  
                    class="follow-button" 
                    @click="toggleFollowUnfollow(profileuser.UserID)"
                    v-if="!(profileuser.UserID === user.UserID)"
                    :class="{
                        'followed-button': profileuser.isFollowedByMe,
                        'unfollow-button': profileuser.isFollowedByMe && isHovered[profileuser.UserID]
                    }"
                    @mouseover="isHovered[profileuser.UserID] = true"
                    @mouseout="isHovered[profileuser.UserID] = false">
                    {{ followButtonLabel(profileuser) }}
                </button>
            </div>
            <div class="profile-details" v-if="profileuser">
                <div class="user-info">
                    <div class="name">{{ profileuser.Name }}</div>
                    <div class="username">{{ profileuser.UserTag }}</div>
                </div>
                <div class="description">{{ profileuser.Description }}</div>
                <div class="join-date"><ion-icon name="calendar-outline" class="callendar-icon"></ion-icon>{{ profileuser.create_date }}</div>
                <div class="follow-info">
                    <p @click="openfollowing(profileuser.UserTag)"><span class="bold">{{ profileuser.following_count }}</span>Following</p>
                    <p @click="openfollowers(profileuser.UserTag)"><span class="bold">{{ profileuser.follower_count }}</span>Followers</p>
                </div>
            </div>
        </div>
        <div class="post-type">
            <button @click="switchToTweets" class="post-type-btn" :class ="{ 'active-post-type': postType == 'posts' }">Posts<div class="active-line" :class ="{ 'active': postType == 'posts' }"></div></button>
            <button @click="switchToReplies" class="post-type-btn" :class ="{ 'active-post-type': postType == 'replies' }">Replies<div class="active-line" :class ="{ 'active': postType == 'replies' }"></div></button>
            <button @click="switchToLikes" class="post-type-btn" :class ="{ 'active-post-type': postType == 'likes' }">Likes<div class="active-line" :class ="{ 'active': postType == 'likes' }"></div></button>
        </div>
        <div class="post-container">
            <div class="post" v-for="tweet in currentPosts" :key="tweet.TweetID"  @click="openTweet(tweet.TweetID)">
                <div class="isretweet" v-if="tweet.isRetweet && this.postType==='posts'">
                    <p class="tweet-text"><span>{{ profileuser.UserTag }}</span> Reposted</p>
                </div>
                <div class="inner-post">
                    <div class="left-side">
                        <img @click.stop="openProfile(tweet.user.UserTag)" :src="'/storage/' + tweet.user.ProfilePicture">
                        <div class="horizontal-comment-line" v-if="postType === 'replies'"></div>
                    </div>
                    <div class="right-side">
                        <div class="top2">
                            <div class="person-image">
                                <img @click.stop="openProfile(tweet.user.UserTag)" :src="'/storage/' + tweet.user.ProfilePicture">
                                <div class="horizontal-comment-line" v-if="postType === 'replies'"></div>
                            </div>
                            <div class="info-content">
                                <div class="userinfo">
                                    <p class="username">{{tweet.user.Name}}</p>
                                    <p class="usertag">{{tweet.user.UserTag}}</p>
                                    <p class="time-posted">{{ tweet.created_ago }}</p>
                                </div>
                                <div class="content-text">
                                    <p v-if="tweet.TweetText" v-html="(tweet.TweetText)"></p>
                                </div>
                            </div>
                        </div>
                        <div class="post-top">
                            <div class="userinfo">
                                <p class="username">{{tweet.user.Name}}</p>
                                <p class="usertag">{{tweet.user.UserTag}}</p>
                                <p class="time-posted">{{ tweet.created_ago }}</p>
                            </div>
                            <div class="content-text">
                                <p v-if="tweet.TweetText" v-html="(tweet.TweetText)"></p>
                            </div>
                        </div>
                        <div class="content-img">
                            <img v-if="tweet.TweetImage" :src="'/storage/' + tweet.TweetImage"/>
                        </div>
                        <div class="bottom">
                            <button class="post-btn-container heart-btn" @click.stop="toggleLike(tweet.TweetID)">
                                <div class="icon-container"><ion-icon :name="tweet.isLiked ? 'heart' : 'heart-outline'" class="post-icon" :class ="{ 'liked': tweet.isLiked }"></ion-icon></div>
                                <p class="post-btn-nr" :class ="{ 'liked': tweet.isLiked }">{{ tweet.like_count }}</p>
                            </button>
                            <button class="post-btn-container comment-btn" @click.stop="tweetIdInPopup = tweet.TweetID; TogglePopup('CommentTrigger')">
                                <div class="icon-container"><ion-icon name="chatbox-outline" class="post-icon"></ion-icon></div>
                                <p class="post-btn-nr">{{ tweet.comment_count }}</p>
                            </button>
                            <button class="post-btn-container retweet-btn" @click.stop="toggleRetweet(tweet.TweetID)">
                                <div class="icon-container"><ion-icon :name="tweet.isRetweeted ? 'arrow-redo' : 'arrow-redo-outline'" class="post-icon" :class ="{ 'retweeted': tweet.isRetweeted }"></ion-icon></div>
                                <p class="post-btn-nr" :class ="{ 'retweeted': tweet.isRetweeted }">{{ tweet.retweet_count }}</p>
                            </button>
                            <button class="post-btn-container bookmark-btn" @click.stop="toggleBookmark(tweet.TweetID)">
                                <div class="icon-container">
                                    <ion-icon :name="tweet.isBookmarked ? 'bookmark' : 'bookmark-outline'" class="post-icon" :class="{ 'bookmarked': tweet.isBookmarked }"></ion-icon>
                                </div>
                                <p class="post-btn-nr" :class="{ 'bookmarked': tweet.isBookmarked }"></p>
                            </button>
                        </div>
                        <button class="delete-btn" @click.stop="deleteTweetID = tweet.TweetID; TogglePopup('DeleteTrigger')" v-if="this.postType=== 'posts' && profileuser.UserID === tweet.user.UserID && profileuser.UserID === user.UserID">
                            <ion-icon name="trash-bin-outline" class="delete-icon"></ion-icon>
                        </button>
                    </div>
                </div>
                <div class="vertical-comment-line" v-if="postType === 'replies'"></div>
                <div class="comment-container">
                    <div class="comment" v-for="comment in tweet.comments" :key="comment.CommentID" v-if="profileuser">
                        <div class="left">
                            <img :src="'/storage/' + profileuser.ProfilePicture">
                            <div class="horizontal-comment-line" v-if="postType === 'replies'"></div>
                            <div class="content">
                                <div class="userinfo">
                                    <p class="username">{{ profileuser.Name }}</p>
                                    <p class="usertag">{{ profileuser.UserTag }}</p>
                                    <p class="time-posted">{{ comment.created_ago }}</p>
                                </div>
                                <div class="content-text">
                                    <p v-if="comment.CommentText" v-html="(comment.CommentText)"></p>
                                </div>
                            </div>
                        </div>
                        <button class="delete-btn" @click.stop="deleteCommentTweetID = comment.TweetID; deleteCommentID = comment.CommentID; TogglePopup('DeleteTrigger2')" v-if="profileuser.UserID === user.UserID">
                            <ion-icon name="trash-bin-outline" class="delete-icon"></ion-icon>
                        </button>
                    </div>
                </div>
            </div>
            <div class="no-posts" v-if="profileuser">{{ profileuser.UserTag }} currently has no {{ noMorePosts() }} {{ postType }}</div>
        </div>
        <Popup v-if="popupTriggers.CommentTrigger" :TogglePopup="() => TogglePopup('CommentTrigger')">
            <div class="create-tweet-or-comment-popup">
                <div class="top">
                    <div class="left-side-popup">
                        <img :src="'/storage/' + user.ProfilePicture" @click="openProfile(user.UserTag)">
                    </div>
                    <div class="right-side-popup">
                        <div class="userinfo-popup">
                            <p class="username">{{ user.Name }}</p>
                            <p class="usertag">{{ user.UserTag }}</p>
                        </div>
                        <div class="tweet-input-container">
                            <textarea v-model="comment_text_input" id="tweet-input-comment" class="tweet-input" rows="1" placeholder="Post your reply" @input="autoSize('commentInput')" ref="commentInput" maxlength="255"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bottom">
                    <div class="buttons"></div>
                    <button class="popup-button" @click="createComment(tweetIdInPopup, comment_text_input)" :disabled="buttonDisabled || !comment_text_input">Comment</button>
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.EditTrigger" :TogglePopup="() => TogglePopup('EditTrigger')">
            <div class="edit-popup">
                <p class="title">Edit profile</p>
                <button class="save-btn" @click="updateProfile()">Save</button>
                <div class="content">
                    <div class="banner">
                        <img v-if="profileuser.Banner || NewBannerImage" class="banner-image" :src="NewBannerImage ? NewBannerImage : '/storage/' + profileuser.Banner">
                        <div v-if="!profileuser.Banner && !NewBannerImage" class="banner-image"></div>
                        <div class="banner-input">
                            <button class="image-btn">
                                <input type="file" id="banner-img-input" @change="onImageChange($event, 'bannerCropper')" hidden>
                                <label for="banner-img-input" class="image-label">
                                    <ion-icon name="images-outline" class="image-icon"></ion-icon>
                                </label>
                            </button>
                        </div>
                        <div class="profile-picture">
                            <img class="profile-img" :src="NewProfileImage ? NewProfileImage : '/storage/' + profileuser.ProfilePicture">
                            <div class="pfp-input">
                                <button class="image-btn">
                                    <input type="file" id="pfp-img-input" @change="onImageChange($event, 'profileCropper')" hidden>
                                    <label for="pfp-img-input" class="image-label">
                                        <ion-icon name="images-outline" class="image-icon"></ion-icon>
                                    </label>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="edit-name">
                        <div class="input-wrap">
                            <input type="text" id="name-input" class="Edit-Input" autocomplete="off" maxlength="30" v-model="NewName" required>
                            <label for="name-input">Name</label>
                        </div>
                        <div v-if ="NameError" class="warning-1">{{ NameError }}</div>
                    </div>
                    <div class="edit-description">
                        <div class="textarea-wrap">
                            <textarea id="desc-input" class="Edit-Textarea" rows="1" @input="autoSize('DescInput')" ref="DescInput" v-model="NewDescription" maxlength="255" required></textarea>
                            <label for="desc-input">Description</label>
                        </div>
                        <div v-if ="NameError" class="warning-1">{{ NameError }}</div>
                    </div>
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.BannerTrigger" :TogglePopup="() => TogglePopup('BannerTrigger')">
            <div class="edit-popup">
                <p class="title">Crop banner</p>
                <button class="save-btn" @click="saveCroppedBanner">Crop</button>
                <div class="content">
                    <cropper
                        ref="bannerCropper"
                        :aspect-ratio="15 / 5"
                        :view-mode="3"
                        :drag-mode="'move'"
                        :guides="false"
                        :img-style="{ 'min-width': '100%', 'max-height': '500px' }"
                        :auto-crop-area="1"
                    />
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.PFPTrigger" :TogglePopup="() => TogglePopup('PFPTrigger')">
            <div class="edit-popup">
                <p class="title">Crop profile picture</p>
                <button class="save-btn" @click="saveCroppedProfile">Crop</button>
                <div class="content" style="min-width: 100%; min-height:100%">
                    <cropper
                        class="profileCropper"
                        ref="profileCropper"
                        :aspect-ratio="1 / 1"
                        :view-mode="3"
                        :drag-mode="'move'"
                        :guides="false"
                        :img-style="{ 'min-width': '100%', 'max-height': '500px' }"
                        :auto-crop-area="1"
                    />
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.DeleteTrigger" :TogglePopup="() => TogglePopup('DeleteTrigger')">
            <div class="delete-popup">
                <h1 class="delete-title">Delete Post</h1>
                <p class="tweet-p">Are you sure you want to delete this post?</p>
                <div class="tweet-buttons">
                    <button class="cancel-button" @click="TogglePopup('DeleteTrigger')">Cancel</button>
                    <button class="delete-button" @click.stop="deleteTweet(deleteTweetID)">Delete</button>
                </div>
            </div>
        </Popup>
        <Popup v-if="popupTriggers.DeleteTrigger2" :TogglePopup="() => TogglePopup('DeleteTrigger2')">
            <div class="delete-popup">
                <h1 class="delete-title">Delete Comment</h1>
                <p class="tweet-p">Are you sure you want to delete this comment?</p>
                <div class="tweet-buttons">
                    <button class="cancel-button" @click="TogglePopup('DeleteTrigger2')">Cancel</button>
                    <button class="delete-button" @click="deleteComment(deleteCommentTweetID, deleteCommentID)">Delete</button>
                </div>
            </div>
        </Popup>
    </div>
</template>

<script>
    import { ref } from 'vue';
    import Popup from '../Popup.vue';
    import { useStore } from 'vuex';
    import { useRouter } from 'vue-router';
    import axios from 'axios';
    import { mapState } from 'vuex';
    import 'cropperjs/dist/cropper.css';
    import VueCropper from 'vue-cropperjs';

    export default{
        name: 'Profile',

        components: {
            Popup,
            cropper: VueCropper,
        },

        data(){
            return {
                isHovered: [],
                deleteTweetID: null,
                deleteCommentTweetID: null,
                deleteCommentID: null,
                comments: [],
                loading: false,
                scrollPositions: {
                    tweets: 0,
                    likes: 0,
                    replies: 0,
                },
                currentPage: {
                    tweets: 1,
                    likes: 1,
                    replies: 1,
                },
                reachedLastPage: {
                    tweets: false,
                    likes: false,
                    replies: false,
                },
                tweets: {
                    tweets: [],
                    likes: [],
                    replies: [],
                },
            }
        },

        computed:{
            ...mapState(['user']),
            followButtonLabel() {
                return (person) => {
                    if (this.isHovered[person.UserID] && person.isFollowedByMe) {
                        return 'Unfollow';
                    } else if (person.isFollowedByMe) {
                        return 'Following';
                    } else {
                        return 'Follow';
                    }
                };
            },

            currentPosts() {
                if (this.postType === 'posts') {
                    return this.tweets.tweets;
                }
                if (this.postType === 'likes') {
                    return this.tweets.likes;
                }
                if (this.postType === 'replies') {
                    return this.tweets.replies;
                }
                return [];
            }
        },

        setup() {
            const tweet_count = ref(0);
            const like_count = ref(0);
            const reply_count = ref(0);
            const profileuser = ref(null);
            const postType = ref('posts');
            const comment_text_input = ref('');
            const NewBannerImage = ref(null);
            const NewProfileImage = ref(null);
            const NewName = ref('');
            const NewDescription = ref('');
            const commentInput = ref(null);
            const popupTriggers = ref({
                CommentTrigger: false,
                EditTrigger: false,
                BannerTrigger: false,
                PFPTrigger: false,
                DeleteTrigger: false,
                DeleteTrigger2: false,
            });

            const router = useRouter();
            const store = useStore();

            const logoutUser = async () => {
                try {
                    await store.dispatch('logout');
                    router.push('/');
                } catch (error) {
                    console.error(error);
                }
            };

            const TogglePopup = (trigger) => {
                if (trigger === 'EditTrigger') {
                    NewName.value= profileuser.value.Name;
                    NewDescription.value= profileuser.value.Description;
                }
                popupTriggers.value[trigger] = !popupTriggers.value[trigger];
                if (trigger === 'CommentTrigger') {
                    comment_text_input.value='';
                }
            };

            return {
                tweet_count,
                like_count,
                reply_count,
                profileuser,
                postType,
                comment_text_input,
                NewBannerImage,
                NewProfileImage,
                NewName,
                NewDescription,
                popupTriggers,
                TogglePopup,
                logoutUser,
                commentInput,
            };
        },

        methods: {
            async updateFollowerCount(user) {
                try {
                    const response = await axios.get(`/api/update-follower-count/${user.UserID}`);

                    const updatedFollowerData = response.data;

                    if (updatedFollowerData) {
                        user.follower_count = updatedFollowerData.follower_count;
                        user.following_count = updatedFollowerData.following_count;
                    } else {
                        console.error('Failed to update follower count');
                    }
                }
                catch (error) {
                    console.error('Error updating follower count:', error);
                }
            },

            async updateCounts(tweets) {
                try {
                    const tweetIds = tweets.map(tweet => tweet.TweetID);
                    const response = await axios.get(`/api/update-stats`, {
                        params: {
                            tweetIds: tweetIds,
                        }
                    });

                    const updatedStatsMap = response.data;

                    tweets.forEach(tweet => {
                        const updatedStats = updatedStatsMap[tweet.TweetID];
                        if (updatedStats) {
                            tweet.like_count = updatedStats.like_count;
                            tweet.comment_count = updatedStats.comment_count;
                            tweet.retweet_count = updatedStats.retweet_count;
                            tweet.created_ago = updatedStats.created_ago;
                        }
                    });

                } catch (error) {
                    console.error('Error updating counts:', error);
                }
            },

            switchToTweets() {
                this.postType = 'posts';
                window.scrollTo(0, this.scrollPositions.tweets);
            },

            switchToReplies() {
                this.postType = 'replies';
                window.scrollTo(0, this.scrollPositions.replies);
            },

            switchToLikes() {
                this.postType = 'likes';
                window.scrollTo(0, this.scrollPositions.likes);
            },

            handleScroll() {
                if (this.reachedLastPage[this.postType] || this.loading) {
                    return;
                }
                const scrollY = window.scrollY;
                const visibleHeight = window.innerHeight;
                const pageHeight = document.documentElement.scrollHeight;

                this.scrollPositions[this.postType] = scrollY;

                if (scrollY + visibleHeight >= pageHeight - 200) {
                    console.log('User has scrolled to the bottom of the page');
                    this.loading = true;
                    this.loadTweets(this.postType, this.currentPage[this.postType]);
                }
            },

            async loadTweets(type) {
                if (this.reachedLastPage[type]) {
                    return;
                }
                try {
                    if (this.currentPage[type] === 1) {
                        this.tweets[type] = [];
                    }

                    const newTweets = await this.loadMoreTweets(type, this.currentPage[type]);
                    this.tweets[type] = this.tweets[type].filter(existingTweet => !newTweets.some(newTweet => newTweet.TweetID === existingTweet.TweetID));

                    this.tweets[type] = [...this.tweets[type], ...newTweets];
                    this.loading = false;
                } catch (error) {
                    console.error('Error loading tweets:', error);
                }
            },

            async loadMoreTweets(type, page) {
                console.log('Loading more tweets:', type, page);

                try {
                    const response = await axios.get(`/api/user_tweets/${this.$route.params.UserTag}/${type}/${page}`);
                    console.log('Full Response Data:', response);

                    if (response.data.tweet_count) {
                        this.tweet_count = response.data.tweet_count
                    }
                    if (response.data.liked_tweet_count) {
                        this.like_count = response.data.liked_tweet_count
                    }
                    if (response.data.commented_tweet_count) {
                        this.reply_count = response.data.commented_tweet_count
                    }

                    const paginatedData = response.data;

                    if (Array.isArray(paginatedData.tweets.data)) {
                        const newTweets = paginatedData.tweets.data;

                        this.currentPage[type]++;

                        if (this.currentPage[type] >= paginatedData.total_pages+1) {
                            this.reachedLastPage[type] = true;
                            console.log('There are no more tweets to load for', type);
                        }
                        console.log('New tweets:', newTweets);
                        return newTweets;
                    } else {
                        console.error('Invalid response format. Tweets should be an array.');
                    }
                } catch (error) {
                    console.error('Error loading more tweets:', error);
                } finally {
                    this.loading = false;
                }
            },

            calculateModifiedPostType() {
                if (this.postType === 'likes') {
                    return this.like_count === 1 ? '1 post liked' : `${this.like_count} posts liked`;
                } else if (this.postType === 'replies') {
                    return this.reply_count === 1 ? '1 post replied to' : `${this.reply_count} posts replied to`;
                } else if (this.postType === 'posts') {
                    return this.tweet_count === 1 ? '1 post' : `${this.tweet_count} posts`;
                } else {
                    return this.postType;
                }
            },

            noMorePosts(){
                if (this.currentPosts.length > 0) {
                    return 'more';
                } else {
                    return ""
                }
            },

            openfollowing(tag){
                const NoSymbolTag = tag.replace(/^@/, '');
                this.$router.push({ name: 'Following', params: { UserTag : NoSymbolTag } });
            },

            openfollowers(tag){
                const NoSymbolTag = tag.replace(/^@/, '');
                this.$router.push({ name: 'Followers', params: { UserTag : NoSymbolTag } });
            },

            toggleFollowUnfollow(userID) {
                if (this.buttonDisabled) {
                    return;
                }
                const user = this.profileuser
                if (user.isFollowedByMe) {
                    this.buttonDisabled = true;
                    this.handleUnfollow(userID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1000);
                } else {
                    this.buttonDisabled = true;
                    this.handleFollow(userID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1000);
                }
            },

            async handleFollow(userID) {
                try {
                    const response = await this.$axios.post(`/api/follow/${userID}`);
                    console.log('Follow Response:', response);
                    if (response.status === 200) {
                        const user = this.profileuser
                        user.isFollowedByMe = true;
                    }
                } catch (error) {
                    console.error('Error following the user:', error);
                }
            },

            async handleUnfollow(userID) {
                try {
                    const response = await this.$axios.post(`/api/unfollow/${userID}`);
                    console.log('Unfollow Response:', response);
                    if (response.status === 200) {
                        const user = this.profileuser
                        user.isFollowedByMe = false;
                    }
                } catch (error) {
                    console.error('Error unfollowing the user:', error);
                }
            },

            async updateProfile() {
                const formData = new FormData();
                const contentType = 'image/png';
                if (this.NewProfileImage) {
                    const blob = await (await fetch(this.NewProfileImage)).blob();
                    const file = new File([blob], 'profile_picture.png', { type: contentType });
                    formData.append('profile_picture', file);
                } else {
                    console.log('No profile image selected.');
                    formData.append('profile_picture', null);
                }
                if (this.NewBannerImage) {
                    const blob = await (await fetch(this.NewBannerImage)).blob();
                    const file = new File([blob], 'banner_picture.png', { type: contentType });
                    formData.append('banner_picture', file);
                } else {
                    console.log('No banner image selected.');
                    formData.append('banner_picture', null);
                }
                formData.append('Name', this.NewName);
                formData.append('Description', this.NewDescription);
                try {
                    const response = await this.$axios.post('/api/update-profile', formData, {
                        headers: {
                        'Content-Type': 'multipart/form-data',
                        },
                    });
                    if (response.data.success) {
                        this.getUserInfo(this.$route.params.UserTag);
                        this.popupTriggers.EditTrigger = false;
                    } else {
                        console.error(response.data.message);
                    }
                } catch (error) {
                    console.error('Error updating profile:', error);
                }
            },

            goBack() {
                this.$router.go(-1);
            },

            autoSize(ref) {
                const maxRows = 8;
                const textarea = this.$refs[ref];
                textarea.style.height = 'auto';
                const customLineHeight = 1;
                const maxHeight = maxRows * customLineHeight * parseFloat(getComputedStyle(textarea).fontSize);

                if (textarea.scrollHeight <= maxHeight) {
                    textarea.style.height = textarea.scrollHeight + 'px';
                } else {
                    textarea.style.height = maxHeight + 'px';
                }
            },

            saveCroppedBanner() {
                const cropper = this.$refs.bannerCropper;

                if (cropper) {
                    const croppedCanvas = cropper.getCroppedCanvas();
                    if (croppedCanvas) {
                        const croppedImageDataURL = croppedCanvas.toDataURL();
                        this.NewBannerImage = croppedImageDataURL;
                        this.popupTriggers.BannerTrigger = false;
                    }
                }
            },

            saveCroppedProfile() {
                const cropper = this.$refs.profileCropper;

                if (cropper) {
                    const croppedCanvas = cropper.getCroppedCanvas();
                    if (croppedCanvas) {
                        const croppedImageDataURL = croppedCanvas.toDataURL();
                        this.NewProfileImage = croppedImageDataURL;
                        this.popupTriggers.PFPTrigger = false;
                    }
                }
            },

            onImageChange(event, cropperRef) {
                if (cropperRef === 'bannerCropper') {
                    this.popupTriggers.BannerTrigger = true;
                } else if (cropperRef === 'profileCropper') {
                    this.popupTriggers.PFPTrigger = true;
                    
                }
                const input = event.target;
                const file = input.files[0];
                console.log('cropperRef:', cropperRef);
                console.log('file:', file);

                this.$nextTick(() => {
                    const cropper = this.$refs[cropperRef];
                    console.log('cropper:', cropper);

                    if (cropper && file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            cropper.replace(e.target.result);
                        };

                        reader.readAsDataURL(file);
                    }
                });
            },

            openProfile(tag){
                const NoSymbolTag = tag.replace(/^@/, '');
                this.$router.push('/profile/' + NoSymbolTag);
                console.log(tag);
            },

            openTweet(id) {
                this.$router.push({ name: 'Posts', params: { tweetID: id } });
                console.log(id);
            },

            deleteTweet(tweetID) {
                this.performDeleteTweet(tweetID);
            },

            async performDeleteTweet(tweetID) {
                if (!tweetID) {
                    console.error('Tweet ID not provided');
                    return;
                }
                try {
                    await this.$axios.delete(`/api/tweets/${tweetID}`);
                    const index = this.currentPosts.findIndex((t) => t.TweetID === tweetID);
                    this.tweet_count -= 1;
                    if (index !== -1) {
                        this.currentPosts.splice(index, 1);
                    }
                    this.popupTriggers.DeleteTrigger = false;
                    console.log(`Tweet with ID ${tweetID} deleted successfully.`);
                } catch (error) {
                    console.error('Error deleting tweet:', error);
                }
            },

            deleteComment(tweetID, commentID) {
                this.performDeleteComment(tweetID, commentID);
            },

            async performDeleteComment(tweetID, commentID) {
                if (!commentID) {
                    console.error('Comment ID not provided');
                    return;
                }
                try {
                    await this.$axios.delete(`/api/delete-comments/${commentID}`);
                    const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                    if (tweet) {
                        tweet.comment_count -= 1;
                        tweet.comments = tweet.comments.filter((c) => c.CommentID !== commentID);
                    }
                    this.popupTriggers.DeleteTrigger2 = false;
                    this.popupTriggers.DeleteTrigger = false;
                    this.loadTweets('replies');
                    console.log(`Comment with ID ${commentID} deleted successfully.`);
                } catch (error) {
                    console.error('Error deleting comment:', error);
                }
            },

            async createComment(tweetID, commentText) {
                if (this.buttonDisabled) {
                    return;
                }
                if (commentText.trim() === '') {
                    return;
                }
                try {
                    this.buttonDisabled = true;
                    const response = await this.$axios.post('/api/create-comments', {
                        tweetId: tweetID,
                        commentText: commentText,
                    });
                    const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                    const newComment = response.data.comment;
                    this.comments.push(newComment);
                    tweet.comment_count++;
                    const textarea = this.commentInput;
                    textarea.style.height = 'auto';
                    this.comment_text_input = '';
                    this.popupTriggers.CommentTrigger = false;
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 2000);
                } catch (error) {
                    console.error('Error creating comment:', error);
                    this.buttonDisabled = false;
                }
            },

            toggleLike(tweetID) {
                if (this.buttonDisabled) {
                    return;
                }
                const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                if (!tweet) {
                    return;
                }
                if (tweet.isLiked) {
                    this.buttonDisabled = true;
                    this.unlikeTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                } else {
                    this.buttonDisabled = true;
                    this.likeTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                }
            },

            async likeTweet(tweetID) {
                try {
                    const response = await this.$axios.post(`/api/tweets/like`, { tweetId: tweetID });
                    console.log('Like Response:', response);
                    if (response.status === 201) {
                        const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                        if (tweet) {
                            tweet.isLiked = true;
                            tweet.like_count += 1;
                        }
                    }
                } catch (error) {
                    console.error('Error liking the tweet:', error);
                }
            },

            async unlikeTweet(tweetId) {
                try {
                    const response = await this.$axios.delete(`/api/tweets/unlike/${tweetId}`);
                    console.log('Unlike Response:', response);
                    if (response.status === 200) {
                        const tweet = this.currentPosts.find((t) => t.TweetID === tweetId);
                        if (tweet) {
                            tweet.isLiked = false;
                            tweet.like_count -= 1;
                        }
                    }
                } catch (error) {
                    console.error('Error unliking the tweet:', error);
                }
            },

            toggleRetweet(tweetID) {
                if (this.buttonDisabled) {
                    return;
                }
                const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                if (!tweet) {
                    return;
                }
                if (tweet && tweet.user.UserTag === this.user.UserTag) {
                    return;
                }
                
                if (tweet.isRetweeted) {
                    this.buttonDisabled = true;
                    this.unretweetTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                } else {
                    this.buttonDisabled = true;
                    this.retweetTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                }
            },

            async retweetTweet(tweetID) {
                try {
                    const response = await this.$axios.post(`/api/tweets/retweet`, { tweetId: tweetID });

                    console.log('Retweet Response:', response);

                    if (response.status === 201) {
                        const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                        if (tweet) {
                            tweet.isRetweeted = true;
                            tweet.retweet_count += 1;
                        }
                    }
                } catch (error) {
                    console.error('Error retweeting the tweet:', error);
                }
            },

            async unretweetTweet(tweetId) {
                try {
                    const response = await this.$axios.delete(`/api/tweets/unretweet/${tweetId}`);

                    console.log('Unretweet Response:', response);

                    if (response.status === 200) {
                        const tweet = this.currentPosts.find((t) => t.TweetID === tweetId);
                        if (tweet) {
                            tweet.isRetweeted = false;
                            tweet.retweet_count -= 1;
                        }
                    }
                } catch (error) {
                    console.error('Error unretweetin the tweet:', error);
                }
            },

            toggleBookmark(tweetID) {
                if (this.buttonDisabled) {
                    return;
                }
                const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                if (!tweet) {
                    return;
                }
                if (tweet.isBookmarked) {
                    this.buttonDisabled = true;
                    this.removeBookmark(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                } else {
                    this.buttonDisabled = true;
                    this.createBookmark(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                }
            },

            async createBookmark(tweetID) {
                try {
                    const response = await this.$axios.post(`/api/tweets/bookmark`, { tweetId: tweetID });
                    console.log('Bookmark Response:', response);
                    if (response.status === 201) {
                        const tweet = this.currentPosts.find((t) => t.TweetID === tweetID);
                        if (tweet) {
                            tweet.isBookmarked = true;
                        }
                    }
                } catch (error) {
                    console.error('Error bookmarking the tweet:', error);
                }
            },

            async removeBookmark(tweetId) {
                try {
                    const response = await this.$axios.delete(`/api/tweets/unbookmark/${tweetId}`);
                    console.log('Unbookmark Response:', response);
                    if (response.status === 200) {
                        const tweet = this.currentPosts.find((t) => t.TweetID === tweetId);
                        if (tweet) {
                            tweet.isBookmarked = false;
                        }
                    }
                } catch (error) {
                    console.error('Error unbookmarking the tweet:', error);
                }
            },

            getSpecificUserTweets(userTag) {
                this.$axios.get(`/api/user-tweets/${userTag}`)
                .then((response) => {
                    this.tweets = response.data.tweets;
                    this.tweet_count = response.data.tweet_count;
                })
                .catch((error) => {
                    console.error(error);
                });
            },

            getUserInfo(userTag){
                this.$axios.get('/api/get-user-tag/' + userTag)
                .then(response => {
                    this.profileuser = response.data.user;
                })
                .catch(error => {
                    console.error(error);
                });
            },

            getLikedTweets(userTag) {
                this.$axios.get(`/api/liked-tweets/${userTag}`)
                .then((response) => {
                    this.liked_tweets = response.data.tweets;
                    this.like_count = response.data.tweet_count;
                })
                .catch((error) => {
                    console.error(error);
                });
            },

            getCommentedTweets(userTag) {
                this.$axios.get(`/api/commented-tweets/${userTag}`)
                .then((response) => {
                    this.replies = response.data.tweets;
                    this.reply_count = response.data.tweet_count;
                })
                .catch((error) => {
                    console.error(error);
                });
            },
        },

        watch: {
            '$route.params.UserTag'(newUserTag) {
                window.location.reload();
            },
        },

        async mounted() {
            await this.$store.dispatch('initializeApp');
            this.getUserInfo(this.$route.params.UserTag);
            this.loadTweets('tweets')
            this.loadTweets('likes')
            this.loadTweets('replies')
            window.addEventListener('scroll', this.handleScroll);
            this.NewTweetInterval = setInterval(() => {
                this.updateCounts(this.currentPosts);
                this.updateFollowerCount(this.profileuser);
            }, 10000);
        },

        beforeDestroy() {
            window.removeEventListener('scroll', this.handleScroll);
            clearInterval(this.NewTweetInterval);
        },

        beforeUnmount() {
            window.removeEventListener('scroll', this.handleScroll);
            clearInterval(this.NewTweetInterval);
        },
        
        beforeRouteLeave(to, from, next) {
            window.removeEventListener('scroll', this.handleScroll);
            clearInterval(this.NewTweetInterval);
        },
    }
</script>

<style lang="scss" scoped>
    .top-bar{
        height:60px;
        width:100%;
        background-color:rgba($color: white, $alpha: 0.8);
        border-bottom:solid 1px #2F3336;
        position:sticky;
        top:0;
        z-index:9;
        box-sizing: border-box;
        backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        padding:0 10px;

        .back-icon{
            display:flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            width:40px;
            height:40px;
            border-radius: 50%;
            border:none;
            font-size:22px;
            background:none;
            color:black;
            cursor:pointer;
            transition: all 0.3s;
            &:hover{
                background-color: rgba($color: #f2f2f2, $alpha: 1);
            }
        }
        .profile-top{
            height:100%;
            width:100%;
            display:flex;
            flex-direction: column;
            padding:10px;
            padding: 10px 10px 0px 30px;
            box-sizing: border-box;
            overflow: hidden;
            .title{
                width:100%;
                height:50%;
                color:black;
                box-sizing: border-box;
                font-weight: bold;
                font-size: 20px;
                margin:0;
                padding:0;
                max-width:100%;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .tweet_count{
                width:100%;
                height:50%;
                margin:0;
                padding:0;
                font-size: 15px;
                color:#6A6F74;
            }
        }
    }
    .profile-info{
        width:100%;
        height:auto;
        display:flex;
        flex-direction: column;
        box-sizing: border-box;
        .banner{
            aspect-ratio: 15/5;
            width:100%;
            position: relative;
            .banner-image{
                height:100%;
                width:100%;
                border:none;
                background: gray;
            }
            .profile-picture{
                position: absolute;
                width:140px;
                height:140px;
                bottom: -70px;
                left: 20px;
                z-index:5;
                box-sizing: border-box;
                img{
                    height:100%;
                    width:100%;
                    background: white;
                    border-radius: 50%;
                    border:4px solid black;
                }
            }
        }
        .edit-button-div{
            width:100%;
            height:70px + 8px;
            display:flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0px 20px;
            box-sizing: border-box;
            .edit-profile{
                padding:10px 15px; 
                color:black;
                font-size: 17px;
                font-weight: bold;
                border:2px solid black;
                border-radius: 50px;
                background-color: white;
                cursor: pointer;
                &:hover{
                    background-color: #f2f2f2;
                }
            }
        }
        .profile-details{
            width:100%;
            height:auto;
            display:flex;
            flex-direction: column;
            box-sizing: border-box;
            padding:15px;
            gap:15px;
            color: black;
            .user-info{
                width:100%;
                height:auto;
                display:flex;
                flex-direction: column;
                align-items: flex-start;
                box-sizing: border-box;
                .name{
                    font-weight: bold;
                    overflow-wrap: anywhere;
                    font-size: 20px;
                    margin:0;
                    padding:0;
                }
                .username{
                    color: #6A6F74;
                    font-size: 16px;
                    margin:0;
                    padding:0;
                }
            }
            .description{
                font-size: 16px;
                margin:0;
                padding:0;
                overflow-wrap: anywhere;
            }
            .join-date{
                color:#6A6F74;
                font-size: 16px;
                margin:0;
                padding:0;
                display:flex;
                flex-direction: row;
                align-items: center;
                .callendar-icon{
                    font-size:16px;
                    padding-right:5px;
                }
            }
            .follow-info{
                width:100%;
                height:30px;
                display:flex;
                flex-direction: row;
                align-items: center;
                gap:10px;
                p{
                    color:#6A6F74;
                    font-size: 16px;
                    margin:0;
                    padding:0;
                    cursor:pointer;
                    &:hover{
                        color:#92969a;
                        border-bottom:1px solid #92969a;
                        .bold{
                            color:black;
                        }
                    }
                    .bold{
                        color:black;
                        font-weight: bold;
                        padding-right:5px;
                    }
                }
            }
        }
    }
    .post-type{
        width:100%;
        height:50px;
        display:flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-evenly;
        box-sizing: border-box;
        margin-top:10px;
        border-bottom:solid 1px #2F3336;
        .post-type-btn{
            height:100%;
            width:100%;
            position:relative;
            align-items: center;
            justify-content: center;
            color:#71767B;
            padding:10px;
            border:none;
            background:none;
            transition:all 0.3s;
            font-size: 16px;
            font-weight:600;
            cursor: pointer;
            &:hover{
                background-color:rgba($color: #f2f2f2, $alpha: 0.8);
            }
        }
        .active-post-type{
            color:black;
            font-weight: 700;
        }
        .active-line{
            height:4px;
            width:75px;
            background-color: #1da1f2;
            border-radius:5px;
            position:absolute;
            bottom:1px;
            display:none;
            left: 50%;
            transform: translateX(-50%);
            &.active{
                display:block;
            }
        }
    }
    .vertical-comment-line{
        position:absolute;
        bottom: 0px;
        left:5px;
        top: 40px;
        width:1px;
        background-color: #211f1f;
        z-index: 1;
    }
    .horizontal-comment-line{
        position:absolute;
        height:1px;
        width:50px;
        top:40px;
        left:5px;
        z-index:1;
        background-color: #211f1f;
    }
    .edit-popup{
        width:500px;
        min-height:550px;
        display:flex;
        flex-direction:column;
        gap:10px;
        padding:50px 20px 10px 20px;
        box-sizing: border-box;
        .title{
            margin:0;
            padding:0;
            font-weight: bold;
            font-size: 22px;
            color: black;
        }
        .save-btn{
            position:absolute;
            top:10px;
            right:10px;
            width:auto;
            height:auto;
            display:flex;
            align-items: center;
            justify-content: center;
            color:white;
            border:none;
            background-color: #1da1f2;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            padding:8px 16px;
            cursor: pointer;
            &:hover{
                background-color: #2394db;
            }
        }
        .content{
            width:100%;
            height:auto;
            display:flex;
            flex-direction: column;
            gap:10px;
            box-sizing: border-box;
            .banner{
                aspect-ratio: 15/5;
                width:100%;
                position: relative;
                box-sizing: border-box;
                .banner-image{
                    height:100%;
                    width:100%;
                    border:none;
                    background: gray;
                }
                .banner-input{
                    position:absolute;
                    top:50%;
                    left:50%;
                    transform: translate(-50%, -50%);
                }
                .profile-picture{
                    position: absolute;
                    width:110px;
                    height:110px;
                    bottom: -60px;
                    left: 20px;
                    box-sizing: border-box;
                    .profile-img{
                        height:100%;
                        width:100%;
                        background: white;
                        border-radius: 50%;
                        box-sizing: border-box;
                        border:4px solid black;
                    }
                    .pfp-input{
                        position:absolute;
                        top:50%;
                        left:50%;
                        transform: translate(-50%, -50%);
                    }
                }
                .image-btn{
                    width:40px;
                    height:40px;
                    border-radius:50%;
                    border:none;
                    background-color:rgba($color: #000000, $alpha: 0.5);
                    margin: 0;
                    padding: 0;
                    &:hover{
                        background-color:rgba($color: #000000, $alpha: 0.6);
                    }
                    .image-label{
                        width:100%;
                        height:100%;
                        display:flex;
                        justify-content: center;
                        align-items: center;
                        margin: 0;
                        padding: 0;
                        cursor:pointer;
                        .image-icon{
                            color:white;
                            font-size: 18px;
                        }
                    }
                }
            }
            .input-wrap{
                border: none;
                border-radius:6px;
                position:relative;
                width:auto;
                height: 60px;
                box-sizing: border-box;
                display:flex;
                .Edit-Input{
                    box-sizing:border-box;
                    border: 1px solid #434343;
                    color:black;
                    padding: 20px 13px 5px 13px;
                    outline:none;
                    background: none;
                    position:relative;
                    display:flex;
                    width:100%;
                    height:100%;
                    border-radius:6px;
                    font-size:18px;
                    resize: none;
                    overflow: hidden;
                    transition: 0.3s all;
                    &:disabled{
                        color:#808080;
                    }
                    &:focus{
                        border:1px solid #1da1f2;
                    }
                }
                label{
                    font-size:18px;
                    color:#434343;
                    padding:5px;
                    position:absolute;
                    top:15px;
                    left:5px;
                    pointer-events: none;
                    transition: 0.3s all;
                }
                .Edit-Input:focus+label,
                .Edit-Input:disabled+label,
                .Edit-Input:valid+label{
                    font-size:16px;
                    top:5px;
                    left:7px;
                    padding:0 5px 0 5px;
                }
                .Edit-Input:focus+label{
                    color:#1da1f2;
                }
            }
            .edit-name{
                padding-top:60px;
            }
            .textarea-wrap{
                border: none;
                border-radius:6px;
                position:relative;
                width:auto;
                min-height: 60px;
                box-sizing: border-box;
                display:flex;
                border: 1px solid #434343;
                padding-top:26px;
                &:focus-within {
                    border: 1px solid #1da1f2;
                }
                .Edit-Textarea{
                    font-family: Arial, sans-serif;
                    box-sizing:border-box;
                    color:black;
                    padding: 0px 13px 0px 13px;
                    outline:none;
                    background: none;
                    border:none;
                    position:relative;
                    display:flex;
                    width:100%;
                    height:100%;
                    border-radius:6px;
                    font-size:18px;
                    resize: none;
                    transition: 0.3s all;
                    &::-webkit-scrollbar{
                        width:4px;
                    }
                    &::-webkit-scrollbar-thumb{
                        background-color: #2F3336;
                        border-radius: 5px;
                        border:none;
                    }
                    &::-webkit-scrollbar-track{
                        background:none;
                        border:none;
                    }
                    &:disabled{
                        color:#808080;
                    }
                }
                label{
                    font-size:18px;
                    color:#434343;
                    padding:5px;
                    position:absolute;
                    top:15px;
                    left:5px;
                    pointer-events: none;
                    transition: 0.3s all;
                }
                .Edit-Textarea:focus+label,
                .Edit-Textarea:disabled+label,
                .Edit-Textarea:valid+label{
                    font-size:16px;
                    top:5px;
                    left:7px;
                    padding:0 5px 0 5px;
                }
                .Edit-Textarea:focus+label{
                    color:#1da1f2;
                }
            }
        }

    }
    .profileCropper{
        .cropper-crop-box{
            border-radius: 50%;
            overflow: hidden;
        }
    }
    @media (max-width: 500px) {
        .top-bar{
            height:auto;
            .back-icon{
                width:40px;
                height:40px;
                font-size:20px;
            }
            .profile-top{
                padding: 5px 5px 0px 15px;
                .title{
                    font-size: 18px;
                }
                .tweet_count{
                    font-size: 14px;
                }
            }
        }
        .profile-info{
            .banner{
                .profile-picture{
                    width:90px;
                    height:90px;
                    bottom: -45px;
                    left: 10px;
                    img{
                        border:2px solid black;
                    }
                }
            }
            .edit-button-div{
                height:45px + 4px;
                padding: 0px 10px;
                .edit-profile{
                    padding:5px 10px; 
                    font-size: 14px;
                }
            }
            .profile-details{
                padding:10px;
                gap:10px;
                .user-info{
                    .name{
                        font-size: 16px;
                    }
                    .username{
                        font-size: 14px;
                    }
                }
                .description{
                    font-size: 14px;
                }
                .join-date{
                    font-size: 14px;
                    .callendar-icon{
                        font-size:14px;
                        padding-right:4px;
                    }
                }
                .follow-info{
                    gap:8px;
                    p{
                        font-size: 14px;
                        .bold{
                            padding-right:4px;
                        }
                    }
                }
            }
        }
        .post-type{
            height:35px;
            margin-top:5px;
            .post-type-btn{
                padding:5px;
                font-size: 14px;
                font-weight:600;
            }
            .active-line{
                height:3px;
                width:50px;
            }
        }
        .vertical-comment-line{
            bottom: 0px;
            left:6px;
            top: 35px;
            width:1px;
        }
        .horizontal-comment-line{
            height:1px;
            width:30px;
            top:35px;
            left:6px;
        }
        .edit-popup{
            width:100%;
            min-height:450px;
            gap:5px;
            padding:40px 10px 10px 10px;
            .title{
                margin:0;
                padding:0;
                font-weight: bold;
                font-size: 16px;
            }
            .save-btn{
                position:absolute;
                top:5px;
                right:5px;
                font-size: 12px;
                padding:7px 14px;
            }
            .content{
                width:100%;
                height:auto;
                gap:10px;
                .banner{
                    aspect-ratio: 15/5;
                    width:100%;
                    .profile-picture{
                        width:80px;
                        height:80px;
                        bottom: -45px;
                        left: 15px;
                        img{
                            border:3px solid black;
                        }
                    }
                    .image-btn{
                        width:35px;
                        height:35px;
                        .image-label{
                            .image-icon{
                                color:white;
                                font-size: 15px;
                            }
                        }
                    }
                }
                .input-wrap{
                    border-radius:6px;
                    height: 40px;
                    .Edit-Input{
                        padding: 13px 13px 1px 7px;
                        width:100%;
                        height:100%;
                        font-size:13px;
                    }
                    label{
                        font-size:13px;
                        padding:5px;
                        top:5px;
                        left:2px;
                    }
                    .Edit-Input:focus+label,
                    .Edit-Input:disabled+label,
                    .Edit-Input:valid+label{
                        font-size:12px;
                        top:4px;
                        left:2px;
                        padding:0 5px 0 5px;
                    }
                }
                .edit-name{
                    padding-top:40px;
                }
                .textarea-wrap{
                    border-radius:6px;
                    min-height: 40px;
                    padding-top: 17px;
                    .Edit-Textarea{
                        padding: 0px 13px 0px 7px;
                        width:100%;
                        height:100%;
                        font-size:13px;
                    }
                    label{
                        font-size:13px;
                        padding:5px;
                        top:5px;
                        left:2px;
                    }
                    .Edit-Textarea:focus+label,
                    .Edit-Textarea:disabled+label,
                    .Edit-Textarea:valid+label{
                        font-size:12px;
                        top:4px;
                        left:2px;
                        padding:0 5px 0 5px;
                    }
                }
            }

        }
    }
</style>