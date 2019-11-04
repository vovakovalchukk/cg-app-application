define(function(){
   let TOPIC_NAMES = {
       'paperSpace': 'paperSpace'
   };

   let topics = {};

   return {
       getTopics: function() {
           return topics;
       },
       getTopicNames: function() {
           return TOPIC_NAMES;
       }
   }
});