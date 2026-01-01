-- Sample Data for AstroFlux Admin Panel
-- Run this after creating the database to populate with test data

USE astroflux_admin;

-- Insert sample horoscopes for today
SET @today = CURDATE();

INSERT INTO horoscopes (zodiac_sign, period, target_date, content, love_score, career_score, health_score, lucky_number, lucky_color, lucky_time, mood, created_by) VALUES
('aries', 'daily', @today, 'Today brings powerful energy for new beginnings. Your natural leadership shines, making it an excellent day for starting projects or taking initiative. Trust your instincts.', 85, 78, 92, '7, 14, 21', 'Red', '10 AM - 12 PM', 'Energetic', 1),
('taurus', 'daily', @today, 'Financial matters take center stage today. Your practical approach helps you make sound decisions. A surprise opportunity may present itself in the afternoon.', 72, 88, 80, '2, 11, 19', 'Green', '2 PM - 4 PM', 'Grounded', 1),
('gemini', 'daily', @today, 'Communication is your superpower today. Share your ideas freely and connect with others. Your curiosity leads you to interesting discoveries.', 78, 85, 75, '5, 12, 23', 'Yellow', '9 AM - 11 AM', 'Curious', 1),
('cancer', 'daily', @today, 'Focus on home and family brings satisfaction. Your nurturing nature is appreciated by loved ones. Trust your emotional intelligence in decision-making.', 90, 70, 85, '3, 15, 22', 'Silver', '6 PM - 8 PM', 'Caring', 1),
('leo', 'daily', @today, 'Your creativity is at its peak! This is an excellent day to showcase your talents. Recognition and appreciation are heading your way.', 82, 90, 88, '1, 10, 17', 'Gold', '12 PM - 2 PM', 'Confident', 1),
('virgo', 'daily', @today, 'Organization and attention to detail pay off today. Your analytical skills help solve a tricky problem. Health and wellness activities are especially beneficial.', 75, 86, 95, '4, 13, 20', 'Navy Blue', '8 AM - 10 AM', 'Focused', 1),
('libra', 'daily', @today, 'Balance and harmony are within reach. Your diplomatic skills smooth over any conflicts. Social connections bring unexpected joy.', 88, 82, 78, '6, 14, 24', 'Pink', '4 PM - 6 PM', 'Balanced', 1),
('scorpio', 'daily', @today, 'Deep insights come to you today. Trust your intuition in all matters. Transformation is possible if you embrace change with an open heart.', 80, 75, 87, '8, 16, 25', 'Maroon', '10 PM - 12 AM', 'Intense', 1),
('sagittarius', 'daily', @today, 'Adventure calls! Your optimistic outlook attracts positive experiences. Learning something new expands your horizons in exciting ways.', 85, 80, 82, '9, 18, 26', 'Purple', '11 AM - 1 PM', 'Adventurous', 1),
('capricorn', 'daily', @today, 'Your ambitious nature drives you toward success. Hard work pays off, and authority figures take notice. Stay disciplined and focused on your goals.', 70, 95, 80, '10, 19, 28', 'Brown', '7 AM - 9 AM', 'Determined', 1),
('aquarius', 'daily', @today, 'Innovation and originality set you apart today. Your unique perspective solves problems others miss. Connect with like-minded individuals.', 77, 85, 83, '11, 20, 29', 'Electric Blue', '3 PM - 5 PM', 'Inventive', 1),
('pisces', 'daily', @today, 'Your imagination soars today. Creative and spiritual pursuits are favored. Compassion and empathy strengthen your relationships.', 92, 72, 78, '12, 21, 30', 'Sea Green', '8 PM - 10 PM', 'Dreamy', 1);

-- Insert sample tarot cards (Major Arcana)
INSERT INTO tarot_cards (name, card_type, suit, number, emoji, meaning_upright, meaning_reversed, description, keywords) VALUES
('The Fool', 'major_arcana', 'none', 0, '🃏', 'New beginnings, innocence, spontaneity, free spirit', 'Recklessness, taken advantage of, inconsideration', 'The Fool represents new beginnings and a free spirit. This card encourages you to take a leap of faith.', 'new beginnings, innocence, adventure, spontaneity'),
('The Magician', 'major_arcana', 'none', 1, '🔮', 'Manifestation, resourcefulness, power, inspired action', 'Manipulation, poor planning, untapped talents', 'The Magician is all about manifestation and having the tools you need to succeed.', 'manifestation, power, action, resourcefulness'),
('The High Priestess', 'major_arcana', 'none', 2, '🌙', 'Intuition, sacred knowledge, divine feminine, the subconscious', 'Secrets, disconnected from intuition, withdrawal', 'The High Priestess represents intuition and inner wisdom. Trust your inner voice.', 'intuition, wisdom, mystery, subconscious'),
('The Empress', 'major_arcana', 'none', 3, '👑', 'Femininity, beauty, nature, nurturing, abundance', 'Creative block, dependence on others', 'The Empress embodies abundance, nurturing, and creativity. She represents mother nature.', 'abundance, nurturing, fertility, beauty'),
('The Emperor', 'major_arcana', 'none', 4, '🏛️', 'Authority, establishment, structure, father figure', 'Domination, excessive control, lack of discipline', 'The Emperor represents authority and structure. He brings order and stability.', 'authority, structure, control, father figure');

-- Insert sample insights for today
INSERT INTO insights (period, target_date, category, title, content, icon, color_code, created_by) VALUES
('daily', @today, 'cosmic_energy', 'Cosmic Energy for Today', 'The universe is aligning in your favor today. Mars in your communication sector brings dynamic energy to your conversations. This is an excellent day for important meetings.', '✨', '#DAA520', 1),
('daily', @today, 'love', 'Love & Relationships', 'Venus highlights romance and connection. Single? You might meet someone special. Coupled? Plan something romantic for the evening.', '💕', '#E91E63', 1),
('daily', @today, 'career', 'Career & Finance', 'Mercury brings clarity to financial decisions. Great day to review your budget or discuss a raise. Trust your instincts with investments.', '💰', '#06A77D', 1),
('daily', @today, 'health', 'Health & Wellness', 'The Moon encourages self-care today. Listen to your body. Meditation or yoga would be especially beneficial under today\'s cosmic influences.', '🧘', '#3B82F6', 1);

-- Insert weekly insights (for current week)
INSERT INTO insights (period, target_date, category, title, content, icon, created_by) VALUES
('weekly', @today, 'cosmic_energy', 'This Week\'s Overview', 'This week brings powerful transformation energy. The New Moon is perfect for setting intentions. Mercury goes direct, clearing up communication issues.', '🌟', 1),
('weekly', @today, 'career', 'Career Focus', 'Early week favors teamwork. Mid-week brings breakthroughs in projects. Weekend: rest and recharge for next week.', '💼', 1);

-- Insert monthly insights
INSERT INTO insights (period, target_date, category, title, content, icon, created_by) VALUES
('monthly', @today, 'cosmic_energy', 'Monthly Overview', 'This month is about growth and expansion. Jupiter brings opportunities. The Full Moon illuminates your path forward.', '🌙', 1),
('monthly', @today, 'personal_growth', 'Personal Growth', 'Powerful month for self-development. Meditation, journaling, and learning are highly favored. Trust your intuition.', '🌱', 1);

-- Verify data
SELECT 'Horoscopes inserted:', COUNT(*) FROM horoscopes;
SELECT 'Tarot cards inserted:', COUNT(*) FROM tarot_cards;
SELECT 'Insights inserted:', COUNT(*) FROM insights;

COMMIT;
