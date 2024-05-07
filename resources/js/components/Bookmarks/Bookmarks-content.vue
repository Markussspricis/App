<template>
    <div class="content-container">
        <div class="top-top">
            <button class="back-icon" @click="goBack">
                <ion-icon name="arrow-back-outline"></ion-icon>
            </button>
            <div class="top-top-top" v-if="user">
                <p class="title">Bookmarks</p>
                <p class="user-tag">{{ user.UserTag }}</p>
            </div>
        </div>
        <div class="post-container">
            <div class="post" v-for="tweet in tweets" :key="tweet.TweetID"  @click="openTweet(tweet.TweetID)">
                <div class="isretweet" v-if="tweet.isRetweet">
                    <p class="tweet-text"><span>{{ user.UserTag }}</span> Reposted</p>
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
                    </div>
                </div>
            </div>
            <div class="no-posts" v-if="tweets.length === 0">Empty...</div>
        </div>
        <Popup v-if="popupTriggers.CommentTrigger" :TogglePopup="() => TogglePopup('CommentTrigger')">
            <div class="create-tweet-or-comment-popup">
                <div class="top">
                    <div class="left-side-popup">
                        <img  @click="openProfile(user.UserTag)" :src="'/storage/' + user.ProfilePicture">
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
    </div>
</template>

<script>
    import { ref } from 'vue';
    import Popup from '../Popup.vue';
    import { mapState } from 'vuex';
    import { useStore } from 'vuex';
    import axios from 'axios';
    import { useRouter } from 'vue-router';
    
    export default{
        name: 'BookmarksContent',

        components: {
            Popup,
        },

        data() {
            return {
                tweets: [],
                comments: [],
                commentsByTweet: {},
                buttonDisabled:false,
                tweetIdInPopup: null,
                loading:false,
                reachedLastPage: false,
                currentPage: 1,
                scrollPositions:0,
            };
        },

        setup(){
            const comment_text_input = ref('');
            const router = useRouter();
            const store = useStore();
            const commentInput = ref(null);

            if (store.state.isLoggedIn) {
                router.push('/bookmarks');
            }

            const logoutUser = async () => {
                try {
                    await store.dispatch('logout');
                    router.push('/');
                } catch (error) {
                    console.error(error);
                }
            };

            const popupTriggers = ref({
                CommentTrigger: false,
                ProfileTrigger: false,
            });

            const TogglePopup = (trigger) => {
                popupTriggers.value[trigger] = !popupTriggers.value[trigger];
            };

            return {
                popupTriggers,
                TogglePopup,
                logoutUser,
                comment_text_input,
                commentInput,
            }
        },

        computed:{
            ...mapState(['isLoggedIn', 'user']),
        },

        methods: {
            handleScroll() {
                if (this.reachedLastPage || this.loading) {
                    return;
                }
                const scrollY = window.scrollY;
                const visibleHeight = window.innerHeight;
                const pageHeight = document.documentElement.scrollHeight;

                this.scrollPositions = scrollY;

                if (scrollY + visibleHeight >= pageHeight - 200) {
                    console.log('User has scrolled to the bottom of the page');
                    this.loading = true;
                    this.loadTweets('bookmark', this.currentPage);
                }
            },

            async loadTweets(type) {
                if (this.reachedLastPage) {
                    return;
                }
                try {
                    if (this.currentPage === 1) {
                        this.tweets = [];
                    }

                    const newTweets = await this.loadMoreTweets(type, this.currentPage);
                    this.tweets = this.tweets.filter(existingTweet => !newTweets.some(newTweet => newTweet.TweetID === existingTweet.TweetID));

                    this.tweets = [...this.tweets, ...newTweets];
                    this.loading = false;
                } catch (error) {
                    console.error('Error loading tweets:', error);
                }
            },

            async loadMoreTweets(type, page) {
                console.log('Loading more tweets:', type, page);

                try {
                    const response = await axios.get(`/api/tweet_type/${type}/${page}`);
                    console.log('Full Response Data:', response);

                    const paginatedData = response.data;

                    if (Array.isArray(paginatedData.tweets.data)) {
                        const newTweets = paginatedData.tweets.data;

                        this.currentPage++;

                        if (this.currentPage >= paginatedData.total_pages+1) {
                            this.reachedLastPage = true;
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

            getTweets(type) {
                axios.get(`/api/tweets/${type}`)
                .then((response) => {
                    this[type + '_tweets'] = response.data.tweets;
                    if (type === 'bookmark') {
                        this.tweets = response.data.tweets;
                    }
                })
                .catch((error) => {
                console.error(error);
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

            toggleLike(tweetID) {
                if (this.buttonDisabled) {
                    return;
                }
                const tweet = this.tweets.find((t) => t.TweetID === tweetID);
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
                    const response = await axios.post(`/api/tweets/like`, { tweetId: tweetID });
                    console.log('Like Response:', response);
                    if (response.status === 201) {
                        const tweet = this.tweets.find((t) => t.TweetID === tweetID);
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
                    const response = await axios.delete(`/api/tweets/unlike/${tweetId}`);
                    console.log('Unlike Response:', response);
                    if (response.status === 200) {
                        const tweet = this.tweets.find((t) => t.TweetID === tweetId);
                        if (tweet) {
                            tweet.isLiked = false;
                            tweet.like_count -= 1;
                        }
                    }
                } catch (error) {
                    console.error('Error unliking the tweet:', error);
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
                    this.comments.unshift(newComment);
                    tweet.comment_count++;
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

            toggleRetweet(tweetID) {
                if (this.buttonDisabled) {
                    return;
                }
                const tweet = this.tweets.find((t) => t.TweetID === tweetID);
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
                    const response = await axios.post(`/api/tweets/retweet`, { tweetId: tweetID });

                    console.log('Retweet Response:', response);

                    if (response.status === 201) {
                        const tweet = this.tweets.find((t) => t.TweetID === tweetID);
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
                    const response = await axios.delete(`/api/tweets/unretweet/${tweetId}`);

                    console.log('Unretweet Response:', response);

                    if (response.status === 200) {
                        const tweet = this.tweets.find((t) => t.TweetID === tweetId);
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
                const tweet = this.tweets.find((t) => t.TweetID === tweetID);
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
                        const tweet = this.tweets.find((t) => t.TweetID === tweetID);
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
                        const tweet = this.tweets.find((t) => t.TweetID === tweetId);
                        if (tweet) {
                            tweet.isBookmarked = false;
                        }
                        this.tweets = this.tweets.filter((tweet) => tweet.TweetID !== tweetId);
                    }
                } catch (error) {
                    console.error('Error unbookmarking the tweet:', error);
                }
            },
        },

        async mounted() {
            window.addEventListener('scroll', this.handleScroll);
            await this.$store.dispatch('initializeApp');
            this.loadTweets('bookmark');
        },

        beforeDestroy() {
            window.removeEventListener('scroll', this.handleScroll);
        },
    }
</script>

<style lang="scss" scoped>
</style>